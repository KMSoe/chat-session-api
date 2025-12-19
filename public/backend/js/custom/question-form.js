$(document).ready(() => {
    /* Already checked for options */
    checkAction();

    /* Add new option row */
    $("#addOptionRow").click(function () {
        var html = '';
        html += '<div class="input-group mb-5" class="inputOptionFormRow" id="inputOptionFormRow">';
        html += '<div class="input-group-text"><input type="checkbox" name="check_option[]" id="check-option" aria-label="Checkbox for following text input"></div>';
        html += '<input type="text" name="options[]" class="form-control option" id="options" placeholder="輸入選項" autocomplete="off" required>';
        html += '<div class="input-group-append"><button id="removeOptionRow" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> </button>';
        html += '</div>';
        html += '</div>';

        $('#newOptionRow').append(html);
    });

    /* Remove row option */
    $(document).on('click', '#removeOptionRow', function () {
        $(this).closest('#inputOptionFormRow').remove();
        checkAction();
    });

    /* Check and uncheck option value */
    $(document).on('click', '#check-option', function () {
        if ($(this).prop("checked") == true) {
            $(this).val(1)
            checkAction();
        }
        else if ($(this).prop("checked") == false) {
            $(this).val(0)
            checkAction();
        }
    })

    $(document).on('keyup', '#options', function() {
        setTimeout(checkAction, 200);
    })

    function checkAction() {
        var option_checked = $("input[name='check_option[]']").map(function () { return $(this).val(); }).get();
        var options = $("input[name='options[]']").map(function () { return $(this).val(); }).get()

        var selected_options = $.map(option_checked, function (value, index) {
            if (value == 1) {
                if (options[index].length > 0) return options[index];
            }
        });

        var checked_answer = $.map(option_checked, function (val, index) {
            if (val == 1) {
                return index;
            }
        });

        $('#checked-answer').val(checked_answer)

        // $("#answer").val(selected_options.join(', '));
    }

})