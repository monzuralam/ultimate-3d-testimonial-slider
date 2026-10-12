/**
 * Tutorial video: a placeholder until a YouTube URL is configured
 * (uts_link( 'video' ) or the uts_getting_started_video filter). With one,
 * the thumbnail is shown and the youtube-nocookie player loads on click.
 */
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * @param {Object} props
 * @param {string} props.videoId YouTube video ID, or ''.
 */
const VideoEmbed = ( { videoId } ) => {
	const [ playing, setPlaying ] = useState( false );
	const title = __(
		'Watch: set up your testimonial slider in a few minutes',
		'uts'
	);

	let content;
	if ( ! videoId ) {
		content = (
			<div className="uts:relative uts:flex uts:size-full uts:flex-col uts:items-center uts:justify-center uts:gap-4 uts:bg-linear-to-br uts:from-wp-blue uts:to-indigo-900 uts:p-6 uts:text-center">
				<span className="uts:flex uts:size-18 uts:items-center uts:justify-center uts:rounded-full uts:bg-white/15 uts:ring-1 uts:ring-white/30">
					<span
						className="dashicons dashicons-controls-play uts:ml-1 uts:size-9 uts:text-4xl uts:text-white"
						aria-hidden="true"
					/>
				</span>
				<p className="uts:m-0 uts:text-xl uts:font-semibold uts:text-white">
					{ __( 'Video tutorial coming soon', 'uts' ) }
				</p>
				<p className="uts:m-0 uts:max-w-md uts:text-white/80">
					{ __(
						'A short walkthrough of adding testimonials and placing the slider. Until then, the "How to use" guide below covers every step.',
						'uts'
					) }
				</p>
			</div>
		);
	} else if ( playing ) {
		content = (
			<iframe
				className="uts-video-frame"
				src={ `https://www.youtube-nocookie.com/embed/${ encodeURIComponent( videoId ) }?autoplay=1&rel=0` }
				title={ title }
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
				allowFullScreen
			/>
		);
	} else {
		content = (
			<button
				type="button"
				className="uts:group uts:relative uts:block uts:size-full uts:cursor-pointer uts:border-0 uts:bg-transparent uts:p-0"
				aria-label={ `${ __( 'Play video:', 'uts' ) } ${ title }` }
				onClick={ () => setPlaying( true ) }
			>
				<img
					src={ `https://i.ytimg.com/vi/${ encodeURIComponent( videoId ) }/hqdefault.jpg` }
					alt=""
					loading="lazy"
					className="uts:size-full uts:object-cover uts:opacity-90 uts:transition-opacity uts:group-hover:opacity-100"
				/>
				<span className="uts:absolute uts:inset-0 uts:flex uts:items-center uts:justify-center">
					<span className="uts:flex uts:size-18 uts:items-center uts:justify-center uts:rounded-full uts:bg-white/95 uts:shadow-lg uts:transition-transform uts:group-hover:scale-110">
						<span
							className="dashicons dashicons-controls-play uts:ml-1 uts:size-9 uts:text-4xl uts:text-wp-blue"
							aria-hidden="true"
						/>
					</span>
				</span>
			</button>
		);
	}

	return (
		<div className="uts:mt-6 uts:aspect-video uts:w-full uts:max-w-4xl uts:overflow-hidden uts:rounded-lg uts:bg-gray-900">
			{ content }
		</div>
	);
};

export default VideoEmbed;
