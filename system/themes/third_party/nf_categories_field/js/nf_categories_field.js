$(document).ready(function() {

    $(".nf_category_field legend").click(function(){
        var target = "."+$(this).attr('rel');
        $(target).toggle();
    });

    $(".nf_category_field input.filter").keyup(function(){

        // Get the field
        var field = $(this).parent().parent();

        // Retrieve the input field text and reset the count to zero
        var filter = $(this).val(), count = 0;

        // Loop through the categories
        $(field).find('label.category').each(function(){

            // If the list item does not contain the text phrase fade it out
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).not('.exclude').hide();
            // Show the list item if the phrase matches and increase the count by 1
            } else {
                $('.nf_category_field div.group').show();
                $(this).show();
                count++;
            }
        });

        // Update the count
        if (filter) {
            if (count!=1) {
                $(field).find('a > span.count').text("("+count+" matches for current filter)");
            } else {
                $(field).find('a > span.count').text("("+count+" match for current filter)");
            }
        } else {
            $(field).find('a > span.count').text("");
        }
    });

    // Ensure that primary categories are checked too
    $('.nf_category_field input[type="radio"]').click(function(){
        $(this).parent().find('input[type="checkbox"]').prop('checked', true);
    });

});