
function disableIfMinCriteria() {

    this.init = function () {
        this.loopThrough();
        this.piDisable();
    }

    this.loopThrough = function () {
        var parent = this;
        var $ = jQuery;
        $(".product_row").each(function () {
            var product = $(this).data('product');
            var cat = $(this).data('cat');

            if (parent.checkIfMin(product, cat)) {
                parent.disableAddToCart(product, cat);
            }
        });
    }

    this.checkIfMin = function (product, cat) {
        var min_set = false;
        var $ = jQuery;
        var parent = this;
        $("#pisol_sidedish_row_" + product + "_" + cat + " .side-dish-group").each(function () {
            var min = $(this).data('min');
            if (min > 0) {
                min_set = true;
                parent.markGroupUnSatisfied(this);
            }
        });
        return min_set;
    }

    this.disableAddToCart = function (product, cat) {
        var $ = jQuery;
        var product_row_id = '#product_' + product + '_' + cat;
        $(product_row_id + " .add_to_cart_button").prop("disabled", true);
        $(product_row_id + " .add_to_cart_button").addClass('pi-disable');
    }

    this.piDisable = function () {
        var $ = jQuery;
        $(".pi-disable").on("click", function (e) {
            e.preventDefault();
        });
    }

    this.markGroupUnSatisfied = function (side_dish) {
        var $ = jQuery;
        $(".sidedish-group", side_dish).addClass('pi-restricted');
    }
}

function enableIfMinCriteriaReached() {
    this.init = function () {
        this.triggerCheck();
    }

    this.triggerCheck = function () {
        var $ = jQuery;
        var parent = this;
        $(document).on('change', '.dish', function () {
            var product = $(this).data('product');
            var cat = $(this).data('cat');
            var sidedish_row = '#pisol_sidedish_row_' + product + '_' + cat;
            var disabled = false;
            $(sidedish_row + " .side-dish-group").each(function () {
                if (!parent.groupSatisfied(this)) {
                    disabled = true;
                }
            });

            if (disabled) {
                parent.disableAddToCart(product, cat);
            } else {
                parent.enableAddToCart(product, cat);
            }
        });
    }

    this.groupSatisfied = function (group) {
        var $ = jQuery;
        var min = $(group).data('min');
        var selection = 0;
        $(".dish", group).each(function () {
            if ($(this).prop("checked") == true) {
                selection = selection + 1;
            }
        });

        if (selection >= min) {
            this.markGroupSatisfied(group);
            return true;
        }
        this.markGroupUnSatisfied(group);
        return false;
    }

    this.disableAddToCart = function (product, cat) {
        var $ = jQuery;
        var product_row_id = '#product_' + product + '_' + cat;
        $(product_row_id + " .add_to_cart_button").prop("disabled", true);
        $(product_row_id + " .add_to_cart_button").addClass('pi-disable');
    }

    this.enableAddToCart = function (product, cat) {
        var $ = jQuery;
        var product_row_id = '#product_' + product + '_' + cat;
        $(product_row_id + " .add_to_cart_button").prop("disabled", false);
        $(product_row_id + " .add_to_cart_button").removeClass('pi-disable');
    }

    this.markGroupSatisfied = function (side_dish) {
        var $ = jQuery;
        $(".sidedish-group", side_dish).removeClass('pi-restricted');
    }

    this.markGroupUnSatisfied = function (side_dish) {
        var $ = jQuery;
        $(".sidedish-group", side_dish).addClass('pi-restricted');
    }
}

jQuery(function ($) {
    var min_criteria_obj = new enableIfMinCriteriaReached();
    min_criteria_obj.init();
});


