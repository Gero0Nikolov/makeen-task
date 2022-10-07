jQuery(document).ready((event) => {

    if (
        typeof window.maexMainConfigObject !== 'undefined' &&
        typeof window.maexMainConfigObject.fields !== 'undefined'
    ) {
        console.log(window.maexMainConfigObject.fields)
    }
});