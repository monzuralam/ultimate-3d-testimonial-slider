"""Builds the voiceover track (voice.wav) and captions (promo.srt) from the recorded lines.

Put the lines in .wordpress-org/audio/1.m4a ... 8.m4a (one per row of the voiceover table
in todo.md), then run from the plugin root:

    python .wordpress-org/video/mix-audio.py path/to/ffmpeg.exe

Each line is trimmed, placed on the video timeline (PLACE), loudness-normalized to
-16 LUFS / -1.5 dBTP, and captioned from its own pauses (CAPTIONS must match the words).
"""
import json
import os
import re
import subprocess
import sys

FF = sys.argv[1] if len(sys.argv) > 1 else 'ffmpeg'
OUT = os.path.dirname(os.path.abspath(__file__))
AUDIO = os.path.join(os.path.dirname(OUT), 'audio')
DURATION = 62.5  # Same as DURATION in promo.html.

# Where each line's first word lands on the video timeline (seconds).
PLACE = {1: 0.25, 2: 3.85, 3: 11.0, 4: 20.35, 5: 30.5, 6: 42.5, 7: 50.0, 8: 56.6}

# Caption text per line, one entry per group of spoken phrases (phrases are split at the pauses).
CAPTIONS = {
    1: [(1, 'Your clients love your work.'), (1, 'Let the world see it.')],
    2: [(1, 'Meet Ultimate 3D Testimonial Slider.'), (1, 'It turns plain reviews into a stunning 3D showcase.')],
    3: [(2, 'Pick a layout: a 3D slider, a clean grid,'), (1, 'a list, a masonry wall, or an endless scrolling ticker.')],
    4: [(2, 'Choose from eight card styles, from classic to glass and dark,'), (1, 'with star ratings that show only where you add them.')],
    5: [(1, 'Build it in the Shortcode Builder.'), (3, 'Name your slider, pick a style, turn on autoplay,'), (1, 'and watch the live preview update.'), (1, 'Save, and copy your shortcode.')],
    6: [(2, 'Paste it on any page, with any theme or page builder,'), (1, "and it's live on your site.")],
    7: [(2, 'Five layouts, eight styles, star ratings.'), (2, 'Lightweight, accessible and mobile ready.')],
    8: [(1, 'Ultimate 3D Testimonial Slider.'), (1, 'Free on WordPress.org.')],
}


def phrases(path):
    """Spoken phrases [(start, end)] in a clip, split at pauses of 0.25 s or more."""
    log = subprocess.run([FF, '-hide_banner', '-nostats', '-i', path, '-af', 'silencedetect=n=-40dB:d=0.25', '-f', 'null', '-'],
                         capture_output=True, text=True).stderr
    # The lead-in before the first word is shorter than 0.25 s: measure it finely.
    lead = subprocess.run([FF, '-hide_banner', '-nostats', '-i', path, '-af', 'silencedetect=n=-45dB:d=0.02', '-f', 'null', '-'],
                          capture_output=True, text=True).stderr
    first = re.search(r'silence_start: 0(?:\.0+)?\s.*?silence_end: ([\d.]+)', lead, re.S)
    dur = float(re.search(r'Duration: (\d+):(\d+):([\d.]+)', log).group(3)) + 60 * int(re.search(r'Duration: (\d+):(\d+)', log).group(2))
    starts = [float(x) for x in re.findall(r'silence_start: ([\d.]+)', log)]
    ends = [float(x) for x in re.findall(r'silence_end: ([\d.]+)', log)]
    gaps = list(zip(starts, ends + [dur] * (len(starts) - len(ends))))
    out, cursor = [], float(first.group(1)) if first else 0.0
    for s, e in gaps:
        if s > cursor + 0.05:
            out.append((cursor, s))
        cursor = e
    if cursor < dur - 0.05:
        out.append((cursor, dur))
    return out


def srt_time(t):
    ms = int(round(t * 1000))
    return '%02d:%02d:%02d,%03d' % (ms // 3600000, ms // 60000 % 60, ms // 1000 % 60, ms % 1000)


inputs, filters, cues, report = [], [], [], []
for n in range(1, 9):
    path = os.path.join(AUDIO, '%d.m4a' % n)
    ph = phrases(path)
    groups = CAPTIONS[n]
    assert sum(g for g, _ in groups) == len(ph), (n, len(ph), ph)
    onset, speech_end = ph[0][0], ph[-1][1]
    clip_start = max(0.0, onset - 0.04)
    clip_end = speech_end + 0.12
    offset = PLACE[n] - onset  # clip time -> video time
    inputs += ['-i', path]
    i = n - 1
    filters.append(
        '[%d:a]atrim=start=%.3f:end=%.3f,asetpts=PTS-STARTPTS,pan=mono|c0=c0,highpass=f=70,'
        'afade=t=in:d=0.02,afade=t=out:st=%.3f:d=0.08,adelay=%d:all=1[v%d]'
        % (i, clip_start, clip_end, clip_end - clip_start - 0.08, round((clip_start + offset) * 1000), i))
    k = 0
    for count, text in groups:
        seg = ph[k:k + count]
        cues.append([seg[0][0] + offset, seg[-1][1] + offset, text])
        k += count
    report.append('line %d: %.2fs of speech at %.2f-%.2f' % (n, speech_end - onset, PLACE[n], speech_end + offset))

# Captions stay up 0.4 s after the words, but never overlap the next one.
for j, c in enumerate(cues):
    nxt = cues[j + 1][0] - 0.05 if j + 1 < len(cues) else DURATION
    c[1] = min(c[1] + 0.4, nxt)
with open(os.path.join(OUT, 'promo.srt'), 'w', encoding='utf-8', newline='\n') as f:
    for j, (a, b, text) in enumerate(cues, 1):
        f.write('%d\n%s --> %s\n%s\n\n' % (j, srt_time(a), srt_time(b), text))

mix = ';'.join(filters) + ';' + ''.join('[v%d]' % i for i in range(8)) + \
    'amix=inputs=8:normalize=0:duration=longest,apad=whole_dur=%.2f,atrim=end=%.2f' % (DURATION, DURATION)
raw = os.path.join(OUT, 'voice-raw.wav')
subprocess.run([FF, '-y', '-hide_banner', '-loglevel', 'error'] + inputs + ['-filter_complex', mix + '[out]', '-map', '[out]', '-ar', '48000', raw], check=True)

# Two-pass loudness normalization: -16 LUFS integrated, -1.5 dBTP.
log = subprocess.run([FF, '-hide_banner', '-nostats', '-i', raw, '-af', 'loudnorm=I=-16:TP=-1.5:LRA=11:print_format=json', '-f', 'null', '-'],
                     capture_output=True, text=True).stderr
m = json.loads(log[log.rindex('{'):log.rindex('}') + 1])
ln = ('loudnorm=I=-16:TP=-1.5:LRA=11:measured_I=%s:measured_TP=%s:measured_LRA=%s:measured_thresh=%s:offset=%s:linear=true'
      % (m['input_i'], m['input_tp'], m['input_lra'], m['input_thresh'], m['target_offset']))
subprocess.run([FF, '-y', '-hide_banner', '-loglevel', 'error', '-i', raw, '-af', ln + ',aresample=48000', '-ac', '2',
                os.path.join(OUT, 'voice.wav')], check=True)
os.remove(raw)
print('\n'.join(report))
print('cues:', len(cues), 'measured input I:', m['input_i'])
