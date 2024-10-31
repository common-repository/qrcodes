/**
 * Created by pierre on 09/05/2016.
 */
var qrcodes_update_data;

qrcodes_params.correctLevel = eval( qrcodes_params.correctLevel );
jQuery( document ).on( 'ready', function () {
	var qrcode = new QRCode( jQuery( '<div></div>' ).attr( 'id', 'qrcodes-container' ).appendTo( 'body' )[0], qrcodes_params );

	qrcodes_update_data = function ( params ) {
		qrcode.makeCode( params.data );
	};
} );