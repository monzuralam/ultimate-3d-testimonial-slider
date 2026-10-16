/**
 * Renders promo.html to an MP4, frame by frame (deterministic: each frame is
 * drawn with window.seek( seconds ) and captured from headless Chrome).
 *
 * Needs: `npm run build` first (the video uses build/frontend.*), Chrome,
 * Playwright (a dev dependency of @wordpress/scripts) and an ffmpeg binary.
 *
 *   node .wordpress-org/video/render.js --ffmpeg=path/to/ffmpeg.exe
 *   node .wordpress-org/video/render.js --stills=2,7,15        (PNG frames only, for review)
 *
 * Options: --fps=30 --from=0 --to=<seconds, default: the composition's DURATION> --out=.wordpress-org/video/promo.mp4
 */
const path = require( 'path' );
const { spawn } = require( 'child_process' );
const { chromium } = require( path.resolve( __dirname, '../../node_modules/playwright' ) );

const args = Object.fromEntries(
	process.argv.slice( 2 ).map( ( a ) => {
		const [ k, v ] = a.replace( /^--/, '' ).split( '=' );
		return [ k, undefined === v ? true : v ];
	} )
);
const fps = Number( args.fps || 30 );
const from = Number( args.from || 0 );
const out = path.resolve( args.out || path.join( __dirname, 'promo.mp4' ) );
const url = 'file:///' + path.join( __dirname, 'promo.html' ).replace( /\\/g, '/' );

( async () => {
	// YouTube thumbnail (1280×720) from thumbnail.html.
	if ( args.thumbnail ) {
		const thumbBrowser = await chromium.launch( { channel: 'chrome' } );
		const thumb = await thumbBrowser.newPage( { viewport: { width: 1280, height: 720 } } );
		await thumb.goto( 'file:///' + path.join( __dirname, 'thumbnail.html' ).replace( /\\/g, '/' ) );
		await thumb.waitForFunction( () => window.thumbReady );
		await thumb.waitForTimeout( 400 );
		const file = path.resolve( true === args.thumbnail ? path.join( __dirname, 'thumbnail.jpg' ) : args.thumbnail );
		await thumb.screenshot( { path: file, type: 'jpeg', quality: 92 } );
		await thumbBrowser.close();
		console.log( 'wrote', file );
		return;
	}

	const browser = await chromium.launch( { channel: 'chrome' } );
	const page = await browser.newPage( { viewport: { width: 1920, height: 1080 }, deviceScaleFactor: 1 } );
	page.on( 'pageerror', ( e ) => console.error( 'page error:', e.message ) );
	await page.goto( url );
	await page.waitForFunction( () => window.promoReady );
	await page.waitForTimeout( 500 );

	if ( args.stills ) {
		for ( const s of String( args.stills ).split( ',' ).map( Number ) ) {
			await page.evaluate( ( t ) => window.seek( t ), s );
			const file = path.join( path.dirname( out ), `still-${ String( s ).replace( '.', '_' ) }.png` );
			await page.screenshot( { path: file } );
			console.log( 'wrote', file );
		}
		await browser.close();
		return;
	}

	if ( ! args.ffmpeg ) {
		throw new Error( 'Pass --ffmpeg=path/to/ffmpeg.exe' );
	}
	const ff = spawn( args.ffmpeg, [ '-y', '-loglevel', 'error', '-f', 'image2pipe', '-framerate', String( fps ), '-c:v', 'mjpeg', '-i', '-', '-c:v', 'libx264', '-preset', 'slow', '-crf', '18', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', out ], { stdio: [ 'pipe', 'inherit', 'inherit' ] } );

	const to = Number( args.to || ( await page.evaluate( () => window.promoDuration ) ) || 60 );
	const total = Math.round( ( to - from ) * fps );
	const started = Date.now();
	for ( let i = 0; i < total; i++ ) {
		await page.evaluate( ( t ) => window.seek( t ), from + i / fps );
		const frame = await page.screenshot( { type: 'jpeg', quality: 95 } );
		if ( ! ff.stdin.write( frame ) ) {
			await new Promise( ( r ) => ff.stdin.once( 'drain', r ) );
		}
		if ( 0 === i % ( fps * 5 ) ) {
			console.log( `frame ${ i }/${ total } (${ Math.round( ( Date.now() - started ) / 1000 ) }s)` );
		}
	}
	ff.stdin.end();
	await new Promise( ( r, j ) => ff.on( 'close', ( code ) => ( code ? j( new Error( 'ffmpeg exit ' + code ) ) : r() ) ) );
	await browser.close();
	console.log( 'wrote', out );
} )().catch( ( e ) => {
	console.error( e );
	process.exit( 1 );
} );
