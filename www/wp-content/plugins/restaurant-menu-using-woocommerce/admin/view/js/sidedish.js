jQuery(document).ready(function ($) {

    $("#pisol_add_side_dish_group").click(function (event) {
        var group_counter = $("#pisol_sidedish_group_container").data('group-counter');
        event.preventDefault();
        $("#pisol_sidedish_group_container").append(`
        <div class="sidedish_group" data-groupid="`+ group_counter + `" data-sidedishes="0">
            <table class="sidedish-table">
            <tr>
                <td style="vertical-align:bottom;">
                    <p class="form-field" style="margin-bottom:0px;">Side Dish Group Name:<input type="text" required name="sidedish[`+ group_counter + `][group_name]" placeholder="Side Dish Group Name *"></p>
                </td>
                <td style="vertical-align:bottom;"> 
                    <button class="button pisol_add_side_dish">Add Side Dish</button> <button class="button remove_sidedish_group">Remove Side Dish Group</button>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="side_dish_container">

                    </div>
                </td>
            <tr>
                <td>
                    Max Selectable: <input type="number" min="1" required name="sidedish[`+ group_counter + `][max]" placeholder="Maximum number of dish that can be selected*" value="1">
                </td>
                <td>
                    Min Selectable: `+ (!pi_restaurant.is_pro ? '(Buy PRO version for this)' : '') + ` <input type="number" min="0" required name="sidedish[` + group_counter + `][min]" placeholder="Minimum number of dish that has to be selected*" value="0">
                </td>
            </tr>
            </table>
        </div>
        `);
        group_counter++;
        $("#pisol_sidedish_group_container").data('group-counter', group_counter);
    });

    $(document).on('click', '.pisol_add_side_dish', function (event) {
        event.preventDefault();
        var parent = $(this).parent().parent().parent().parent();
        var sidedish_group = parent.parent();
        var group_counter = sidedish_group.data('groupid');
        var sidedishes = sidedish_group.data('sidedishes');

        sidedish_group.data('sidedishes', sidedishes + 1);

        $(".side_dish_container", parent).append(`
            <div class="sidedish_row">
            <input type="text" required name="sidedish[`+ group_counter + `][sidedish][` + sidedishes + `][name]" placeholder="Side Dish Name*">
            <input type="number" min="0" step="0.01" name="sidedish[`+ group_counter + `][sidedish][` + sidedishes + `][price]" placeholder="Side Dish Price">
            <button class="button button-primary remove_sidedish">Remove</button>
            </div>
        `);

    });

    $(document).on('click', '.remove_sidedish_group', function (event) {
        event.preventDefault();
        var parent = $(this).parent().parent().parent().parent();
        parent.remove();
    });

    $(document).on('click', '.remove_sidedish', function (event) {
        event.preventDefault();
        var parent = $(this).parent();
        parent.remove();
    });


})