(function ($, undefined) {
    var shoppingFeed;
    if( !shoppingFeed )
        shoppingFeed = {};
    else if( typeof shoppingFeed != "object" )
        throw new Error( 'fr already exists and not an object' );

    shoppingFeed.accounts = {
        tpl_line : '',
        init : function( ) {'use strict'
            // Init the repeater
            this.init_repeater( );
        },
        init_repeater : function( ) {'use strict'
            this.tpl_line = document.getElementById( 'tpl-line' ).innerHTML;

            // Add listener on the duplicate element
            $( '.account__wrapper' ).on( 'click', '.add_link', function( e ) {
                e.preventDefault( );

                var parent = $( this ).closest( '.block_links' ),
                    last_id = parent.find( 'tbody tr' ).size( );
                // Add the new link to the list
                parent.find( 'table tbody' ).append( _.template( shoppingFeed.accounts.tpl_line )({
                    'row' : last_id,
                    'user' : '',
                    'pass' : ''
                }));

            } ).on( 'click', '.delete_link', function( e ) {
                e.preventDefault( );
                var parent = $( this ).closest( '.block_links' ),
                    size = parent.find( 'tbody tr' ).size( );
                // Remove the closest item

                if (size>1){
                    $( this ).closest( 'tr' ).remove( );
                    shoppingFeed.accounts.remake_inputs( parent );
                }
            } );

        },
        remake_inputs : function( parent ) {
            var content_final = '',
                inputs = parent.find( 'tbody tr' );
            // Recreate all the field with their custom values
            _.each( inputs, function( el, i ) {
                var el = $( el );
                content_final += _.template( shoppingFeed.accounts.tpl_line)({
                    'row' : i,
                    'user' : el.find( '.user' ).val( ),
                    'pass' : el.find( '.pass' ).val( )
                });
            } );

            // Remove all the inputs
            inputs.remove( );

            // Prepend all the previous links
            parent.find( 'tbody' ).html( content_final );
        }
    }
    shoppingFeed.accounts.init( );
})(jQuery);
