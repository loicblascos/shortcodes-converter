const content = document.querySelector( '#convert' ).textContent;
let controller;
let button;
let timer;

addEventListener( 'click', ( { target } ) => {

    const { id, classList } = target;

    if ( [ 'convert', 'restore', 'cleanup' ].includes( id ) ) {
        submit( target, id )
    } else if ( classList.contains( 'abort' ) ) {
        controller && controller.abort()
    }

} );

function submit( target, method ) {

    clearTimeout( timer );

    if ( button && button.oldContent ) {
        button.textContent = button.oldContent;
    }

    button = target;

    if ( ! button.oldContent ) {
        button.oldContent = button.textContent;
    }

    button.textContent = sc_converter.progress;
    convert( 0, method );

}

function convert( offset, method ) {

    const data = new FormData();

    data.append( 'action', 'sc_converter_request' );
    data.append( 'nonce', sc_converter.nonce );
    data.append( 'method', method );
    data.append( 'offset', offset );

    controller && controller.abort();
    controller = new AbortController();

    fetch( sc_converter.ajaxUrl, {
        method : 'POST',
        body   : data,
        signal : controller.signal,
    } ).then( response => {

        const contentType = response.headers.get( 'content-type' );

        if ( ! response.ok ) {
            throw Error( response.statusText );
        }

        if ( ! contentType || contentType.indexOf( 'application/json' ) < 0 ) {
            throw Error( 'Invalid response format' );
        }

        return response.json();

    } )
    .then( response => handle( response, method ) )
    .catch( response => handle( response, method ) );

}

function handle( response, method ) {

    if ( ! response.message ) {

        button.textContent = button.oldContent
        return;

    }

    button.textContent = response.message;

    if ( response.content > 0 ) {
        convert( response.content, method );
    } else {
        timer = setTimeout( () => button.textContent = button.oldContent, 1500 );
    }
}
