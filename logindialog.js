// run when page is ready
$(document).ready(function(){


    // create variable to hold the current modal window
    var activeWindow;


    $('a.modalLink').click(function(e){


        // cancel the default link behaviour
        e.preventDefault();


        // find the href of the link that was clicked to use as an id
        var id = $(this).attr('href');


        // assign the window with matching id to the activeWindow variable, move it to the center of the screen and fade in
        activeWindow = $('.window' + id)
            .css('opacity', '0') // set to an initial 0 opacity
            .css('top', '50%') // position vertically at 50%
            .css('left', '50%') // position horizontally at 50%
            .fadeTo(500, 1); // fade to an opacity of 1 (100%) over 500 milliseconds


        // create blind and fade in
        $('#modal')
            .append('<div id="blind" />') // create a <div> with an id of 'blind'
            .find('#blind') // select the div we've just created
            .css('opacity', '0') // set the initial opacity to 0
            .fadeTo(500, 0.8) // fade in to an opacity of 0.8 (80%) over 500 milliseconds
            .click(function(e){
                closeModal(); // close modal if someone clicks anywhere on the blind (outside of the window)
            });


    });


    $('a.close').click(function(e){
            // cancel default behaviour
            e.preventDefault();


            // call the closeModal function passing this close button's window
            closeModal();
    });     


    function closeModal()
    {


        // fade out window and then move back to off screen when fade completes
        activeWindow.fadeOut(250, function(){ $(this).css('top', '-1000px').css('left', '-1000px'); });


        // fade out blind and then remove it
        $('#blind').fadeOut(250,    function(){ $(this).remove(); });


    }


});