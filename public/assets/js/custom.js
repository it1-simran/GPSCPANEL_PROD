$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    function loadLinkedResellers(cutype, uid) {
        $("#linkResellerAccModal").find(".resellers_error").html("");
        $("#linkResellerAccModal")
            .find("form")
            .attr("action", "/" + cutype + "/linkResellers");

        $.ajax({
            url: "/" + cutype + "/getResellersList",
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: { uid: uid },
            success: function (response) {
                var response = jQuery.parseJSON(response);
                $("#linkResellersList").val("").trigger("change");

                $("#linkResellersList")
                    .select2({
                        placeholder: "Search and Select",
                        allowClear: true,
                        multiple: true,
                        data: response.resellers,
                    })
                    .on("select2-open", function () {
                        $(this)
                            .data("select2")
                            .results.addClass("overflow-hidden")
                            .perfectScrollbar();
                    });

                $("#linkResellerAccModal").find("#user_id").val(uid);
                $("#linkResellerAccModal").find("#cutype").val(cutype);
                $("#linkResellerAccModal").modal("show");
            },
        });
    }

    $("#editDeviceUsers").select2();

    $("#editDeviceUsers").on("change", function () {
        var curr_user = $("body").find(".prev_uid").val();
        var selectedOption = $(this).val();
        if (curr_user != "" && selectedOption != curr_user) {
            $("#deviceUserPreviewModal").modal({
                backdrop: "static",
                keyboard: false,
            });
        }

        //$('#editDeviceUsers').val(107).trigger('change.select2');
    });

    $("body").on("click", ".selectDeviceUserChange", function (e) {
        var element = $(this);
        var dtype = element.attr("data-type");

        if (dtype == "no") {
            var curr_user = $("body").find(".prev_uid").val();
            $("#editDeviceUsers").val(curr_user).trigger("change.select2");
        }

        $("#deviceUserPreviewModal").modal("hide");
    });

    var new_imei_table = "";
    var dup_imei_table = "";

    $("body").on("click", ".submitMultipleDevice", function (e) {
        var element = $(this);
        var parent_form = element.parents("form");
        var parent_modal = $("body").find("#imeiPreviewModal");
        $(".error_msg").html("").hide();

        var hasError = false;
        parent_form.find(".req_error").html("");

        var select2Element = $("#s2example-2");

        parent_form.find(".reqfield").each(function (index, ele) {
            var ele = $(ele);
            if (ele.val() == "") {
                hasError = true;
                ele.parent("div")
                    .find(".req_error")
                    .html("This field is required");
            }
        });

        var fileInput = document.getElementById("excel_file");
        var file = fileInput.files[0];
        var fileName = file.name;
        var fileType = fileName.slice(
            ((fileName.lastIndexOf(".") - 1) >>> 0) + 2
        );

        if (fileType === "xls" || fileType === "xlsx") {
            console.log("File format is Excel:", fileName);
            // Call function or perform operations specific to Excel file
        } else {
            // File is not in Excel format, show error message
            hasError = true;
            alert("Filed should be xls or xlsx format");
            return false;
        }
        var fileSize = file.size; // Size in bytes
        var maxSizeInBytes = 10 * 1024 * 1024; // 10 MB (adjust as needed)

        if (fileSize > maxSizeInBytes) {
            alert(
                "File size exceeds the maximum limit (10MB). Please select a smaller file."
            );
            hasError = true;
            return false;
        }
        if (!select2Element.val()) {
            alert("please select device Category");
            hasError = true;
            return false;
        }
        if (!hasError) {
            const fileInput = $("#excel_file")[0];
            const formData = new FormData();
            formData.append("excel_file", fileInput.files[0]);

            $.ajax({
                url: "/admin/submitImeiSheet",
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    var response = jQuery.parseJSON(response);
                    if (response.error == 403) {
                        $("#error_msg_imei").append(response.error_msg);
                    }
                    // if (response.error == 403) {
                    //     alert(response.error_msg);
                    //     // document.documentElement.scrollIntoView({
                    //     //     behavior: 'smooth',
                    //     //     block: 'start'
                    //     // });
                    //     return;
                    // }
                    parent_modal
                        .find(".total_dup_imei")
                        .html(response.dup_imei);
                    parent_modal
                        .find(".total_new_imei")
                        .html(response.new_imei);

                    if (response.dup_imei > 0) {
                        parent_modal.find(".dup_action_row").show();
                    } else {
                        parent_modal.find(".dup_action_row").hide();
                    }

                    /////// MANAGE NEW IMEI TABLE //////////
                    parent_modal
                        .find(".new_imei_table tbody")
                        .html(response.new_imei_html);
                    new_imei_table = parent_modal
                        .find("#new_imei_table")
                        .DataTable({
                            iDisplayLength: 10,
                            columnDefs: [
                                {
                                    targets: 0,
                                    searchable: false,
                                    orderable: false,
                                },
                            ],
                        });

                    $("body").on("click", "#new_imei_checkall", function (e) {
                        var rows = new_imei_table
                            .rows({ search: "applied" })
                            .nodes();
                        parent_modal
                            .find(
                                '.new_imei_table tbody input[type="checkbox"]',
                                rows
                            )
                            .prop("checked", this.checked);
                    });

                    $("body").on(
                        "change",
                        '#new_imei_table tbody input[type="checkbox"]',
                        function (e) {
                            if (!this.checked) {
                                var el = parent_modal
                                    .find("#new_imei_checkall")
                                    .get(0);
                                if (el && el.checked && "indeterminate" in el) {
                                    el.indeterminate = true;
                                }
                            }
                        }
                    );

                    /////// MANAGE DUPLICATE IMEI TABLE //////////
                    parent_modal
                        .find(".dup_imei_table tbody")
                        .html(response.dup_imei_html);
                    dup_imei_table = parent_modal
                        .find("#dup_imei_table")
                        .DataTable({
                            iDisplayLength: 25,
                            columnDefs: [
                                {
                                    targets: 0,
                                    searchable: false,
                                    orderable: false,
                                },
                            ],
                        });

                    $("body").on("click", "#dup_imei_checkall", function (e) {
                        var rows = dup_imei_table
                            .rows({ search: "applied" })
                            .nodes();
                        parent_modal
                            .find(
                                '.dup_imei_table tbody input[type="checkbox"]',
                                rows
                            )
                            .prop("checked", this.checked);
                    });

                    $("body").on(
                        "change",
                        '#dup_imei_table tbody input[type="checkbox"]',
                        function (e) {
                            if (!this.checked) {
                                var el = parent_modal
                                    .find("#dup_imei_checkall")
                                    .get(0);
                                if (el && el.checked && "indeterminate" in el) {
                                    el.indeterminate = true;
                                }
                            }
                        }
                    );

                    parent_modal.modal({
                        backdrop: "static",
                        keyboard: false,
                    });
                },
            });
        }
    });

    $("body").on("click", ".submitMultipleDeviceSupport", function (e) {
        var element = $(this);
        var parent_form = element.parents("form");
        var parent_modal = $("body").find("#imeiPreviewModal");
        $(".error_msg").html("").hide();
        var $btn = $(this);

        // Disable button
        $btn.prop("disabled", true);

        // Show spinner and hide text
        $btn.find(".btn-text").addClass("d-none");
        $btn.find(".spinner-border").removeClass("d-none");

        var hasError = false;
        parent_form.find(".req_error").html("");

        var select2Element = $("#s2example-2");

        parent_form.find(".reqfield").each(function (index, ele) {
            var ele = $(ele);
            if (ele.val() == "") {
                hasError = true;
                ele.parent("div")
                    .find(".req_error")
                    .html("This field is required");
                $btn.prop("disabled", false);
                $btn.find(".btn-text").removeClass("d-none");
                $btn.find(".spinner-border").addClass("d-none");
            }
        });

        var fileInput = document.getElementById("excel_file");
        if (!fileInput.files || fileInput.files.length === 0) {
            $(".req_error_file").html("This field is required");
            return false; // stop form submit
        } else {
            $(".req_error_file").html(""); // clear error if file is selected
        }
        var file = fileInput.files[0];
        var fileName = file.name;
        var fileType = fileName.slice(
            ((fileName.lastIndexOf(".") - 1) >>> 0) + 2
        );

        if (fileType === "xls" || fileType === "xlsx") {
            //console.log("File format is Excel:", fileName);
            // Call function or perform operations specific to Excel file
        } else {
            // File is not in Excel format, show error message
            hasError = true;
            alert("Filed should be xls or xlsx format");
            $btn.prop("disabled", false);
            $btn.find(".btn-text").removeClass("d-none");
            $btn.find(".spinner-border").addClass("d-none");

            return false;
        }
        var fileSize = file.size; // Size in bytes
        var maxSizeInBytes = 10 * 1024 * 1024; // 10 MB (adjust as needed)

        if (fileSize > maxSizeInBytes) {
            alert(
                "File size exceeds the maximum limit (10MB). Please select a smaller file."
            );
            hasError = true;
            $btn.prop("disabled", false);
            $btn.find(".btn-text").removeClass("d-none");
            $btn.find(".spinner-border").addClass("d-none");

            return false;
        }
        if (!select2Element.val()) {
            alert("please select device Category");
            hasError = true;
            $btn.prop("disabled", false);
            $btn.find(".btn-text").removeClass("d-none");
            $btn.find(".spinner-border").addClass("d-none");

            return false;
        }
        if (!hasError) {
            const fileInput = $("#excel_file")[0];
            const formData = new FormData();
            formData.append("excel_file", fileInput.files[0]);

            $.ajax({
                url: "/support/submit-assign-device",
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    var response = jQuery.parseJSON(response);
                    if (response.error == 403) {
                        $("#error_msg_imei").append(response.error_msg);
                    }
                    // if (response.error == 403) {
                    //     alert(response.error_msg);
                    //     // document.documentElement.scrollIntoView({
                    //     //     behavior: 'smooth',
                    //     //     block: 'start'
                    //     // });
                    //     return;
                    // }
                    parent_modal
                        .find(".total_dup_imei")
                        .html(response.dup_imei);
                    parent_modal
                        .find(".total_new_imei")
                        .html(response.new_imei);

                    if (response.dup_imei > 0) {
                        parent_modal.find(".dup_action_row").show();
                    } else {
                        parent_modal.find(".dup_action_row").hide();
                    }

                    /////// MANAGE NEW IMEI TABLE //////////
                    parent_modal
                        .find(".new_imei_table tbody")
                        .html(response.new_imei_html);
                    new_imei_table = parent_modal
                        .find("#new_imei_table")
                        .DataTable({
                            iDisplayLength: 10,
                            columnDefs: [
                                {
                                    targets: 0,
                                    searchable: false,
                                    orderable: false,
                                },
                            ],
                        });

                    $btn.prop("disabled", false);
                    $btn.find(".btn-text").removeClass("d-none");
                    $btn.find(".spinner-border").addClass("d-none");

                    $("body").on("click", "#new_imei_checkall", function (e) {
                        var rows = new_imei_table
                            .rows({ search: "applied" })
                            .nodes();
                        parent_modal
                            .find(
                                '.new_imei_table tbody input[type="checkbox"]',
                                rows
                            )
                            .prop("checked", this.checked);
                    });

                    $("body").on(
                        "change",
                        '#new_imei_table tbody input[type="checkbox"]',
                        function (e) {
                            if (!this.checked) {
                                var el = parent_modal
                                    .find("#new_imei_checkall")
                                    .get(0);
                                if (el && el.checked && "indeterminate" in el) {
                                    el.indeterminate = true;
                                }
                            }
                        }
                    );

                    /////// MANAGE DUPLICATE IMEI TABLE //////////
                    parent_modal
                        .find(".dup_imei_table tbody")
                        .html(response.dup_imei_html);
                    dup_imei_table = parent_modal
                        .find("#dup_imei_table")
                        .DataTable({
                            iDisplayLength: 25,
                            columnDefs: [
                                {
                                    targets: 0,
                                    searchable: false,
                                    orderable: false,
                                },
                            ],
                        });

                    $("body").on("click", "#dup_imei_checkall", function (e) {
                        var rows = dup_imei_table
                            .rows({ search: "applied" })
                            .nodes();
                        parent_modal
                            .find(
                                '.dup_imei_table tbody input[type="checkbox"]',
                                rows
                            )
                            .prop("checked", this.checked);
                    });

                    $("body").on(
                        "change",
                        '#dup_imei_table tbody input[type="checkbox"]',
                        function (e) {
                            if (!this.checked) {
                                var el = parent_modal
                                    .find("#dup_imei_checkall")
                                    .get(0);
                                if (el && el.checked && "indeterminate" in el) {
                                    el.indeterminate = true;
                                }
                            }
                        }
                    );

                    parent_modal.modal({
                        backdrop: "static",
                        keyboard: false,
                    });
                },
            });
        }
    });

    $("body").on("click", ".submit_sel_imei", function (e) {
        var parent_modal = $("#imeiPreviewModal");
        var element = $(this);

        var new_imei_list = new_imei_table
            .$("input[type=checkbox]:checked")
            .map(function () {
                return $(this).val();
            })
            .get()
            .join(",");

        var dup_imei_list = dup_imei_table
            .$("input[type=checkbox]:checked")
            .map(function () {
                return $(this).val();
            })
            .get()
            .join(",");

        var dup_type = parent_modal
            .find('input[class="selectDupImeiType"]:checked')
            .val();

        var f_html =
            '<input type="hidden" name="new_imei_list" value="' +
            new_imei_list +
            '"><input type="hidden" name="dup_imei_list" value="' +
            dup_imei_list +
            '"><input type="hidden" name="dup_type" value="' +
            dup_type +
            '">';

        $("body").find(".imeifields").html(f_html);

        $("body").find("#commentForm").submit();
    });

    $("body").on("click", ".linkReseller", function (e) {
        var parent_element = $("#linkResellerAccModal");
        var element = $(this);
        var uid = element.attr("data-uid");
        var cutype = element.attr("data-cutype");

        loadLinkedResellers(cutype, uid);
    });

    $("body").on("click", ".submitResellerLink", function (e) {
        var parent_element = $("#linkResellerAccModal");
        var element = $(this);

        var selected_options = parent_element.find("#linkResellersList").val();
        if (selected_options) {
            parent_element.find("form").submit();
        } else {
            parent_element
                .find(".resellers_error")
                .html("Please select atleast one reseller");
        }
    });

    $("body").on("click", "input.selectDelType", function (e) {
        var parent_element = $("#userEditDelOptionsModal");
        var action_type = parent_element.find(".action_type").val();
        var del_type = parent_element
            .find('input[class="selectDelType"]:checked')
            .val();
        parent_element.find(".submitEditDelUserOptions").removeClass("hide");

        if (action_type == "edit") {
            /// FOR EDIT CASE
            if (del_type == "del_all") {
                parent_element
                    .find(".submitEditDelUserOptions")
                    .text("Continue");
            } else {
                parent_element.find(".submitEditDelUserOptions").text("Submit");
            }
        } else {
            parent_element.find(".submitEditDelUserOptions").text("Submit");
        }
    });

    $("body").on("click", "button.submitEditDelUserOptions", function (e) {
        var parent_element = $("#userEditDelOptionsModal");
        var element = $(this);
        var action_type = parent_element.find(".action_type").val();

        var change_type = parent_element.find(".change_type").val();

        if (action_type == "edit") {
            /// FOR EDIT CASE
            if (change_type == "r_t_u") {
                var del_type = parent_element
                    .find('input[class="selectDelType"]:checked')
                    .val();
                if (del_type == "del_all") {
                    if (parent_element.find(".step1").hasClass("hide")) {
                        var shift_type = parent_element
                            .find('input[class="selectShiftType"]:checked')
                            .val();

                        var in_html =
                            '<input type="hidden" name="del_type" value="' +
                            del_type +
                            '">';
                        in_html +=
                            '<input type="hidden" name="shift_type" value="' +
                            shift_type +
                            '">';

                        $("body").find(".userAccCases").html(in_html);
                        $("body").find(".userResellerEditForm").submit();
                    } else {
                        parent_element.find(".step1").addClass("hide");
                        parent_element.find(".step2").removeClass("hide");
                        parent_element.find(".backToStep1").removeClass("hide");
                        element.text("Submit");
                    }
                } else {
                    var in_html =
                        '<input type="hidden" name="del_type" value="' +
                        del_type +
                        '">';
                    in_html +=
                        '<input type="hidden" name="shift_type" value="">';

                    $("body").find(".userAccCases").html(in_html);
                    $("body").find(".userResellerEditForm").submit();
                }
            } else {
                $("body").find(".userResellerEditForm").submit();
            }
        } else if (action_type == "delete") {
            /// FOR DELETE CASE
            var in_html = "";

            if (change_type == "Reseller") {
                var del_type = parent_element
                    .find('input[class="selectDelType"]:checked')
                    .val();
                in_html +=
                    '<input type="hidden" name="del_type" value="' +
                    del_type +
                    '">';
                in_html +=
                    '<input type="hidden" name="user_type" value="' +
                    change_type +
                    '">';
            } else {
                in_html +=
                    '<input type="hidden" name="user_type" value="' +
                    change_type +
                    '">';
            }

            $("body").find(".userAccCases").html(in_html);
            $("body").find(".delUserResellerForm").submit();
        }
    });

    $("body").on("click", "button.backToStep1", function (e) {
        var parent_element = $("#userEditDelOptionsModal");
        parent_element.find(".step1").removeClass("hide");
        parent_element.find(".submitEditDelUserOptions").text("Continue");
        parent_element.find(".step2").addClass("hide");
        $(this).addClass("hide");
    });

    $("body").on("click", ".updateUserSubBtn", function (e) {
        e.preventDefault();
        var can_submit = true;

        var element = $(this);
        var parent_form = element.parents("form");

        var current_utype = parent_form.find(".current_utype").val();
        if (current_utype) {
            if (current_utype != "Admin") {
                var userAccType = parent_form.find(".userAccType").val();
                var prevAccType = parent_form
                    .find(".userAccType")
                    .attr("data-prev");
                if (userAccType != prevAccType) {
                    if (userAccType == "User") {
                        /// Acc Changed from Reseller to User
                        can_submit = false;
                        parent_form.find(".userNewAccType").val("r_t_u");
                    } else {
                        can_submit = false;
                        parent_form.find(".userNewAccType").val("u_t_r");
                    }
                }
            }
        }
        if (can_submit) {
            parent_form.submit();
        } else {
            var parent_element = $("body").find("#userEditDelOptionsModal");

            parent_element.find(".action_type").val("edit");

            if (parent_form.find(".userNewAccType").val() == "u_t_r") {
                parent_element.find(".change_type").val("u_t_r");
                parent_element.find(".step1").addClass("hide");

                parent_element
                    .find(".submitEditDelUserOptions")
                    .removeClass("hide");
                parent_element.find(".submitEditDelUserOptions").text("Submit");

                var html =
                    '<div class="form-group"><label class="control-label"><input checked="checked" class="selectShiftType" type="radio" value="same_account" name="shift_type">Shift all devices as a user to unassigned devices as a reseller account</label></div>';

                parent_element.find(".step2").html(html).removeClass("hide");
            } else {
                parent_element.find(".change_type").val("r_t_u");
            }

            parent_element.find(".closeEditDelOptionsModal").hide();

            $("body").find("#userEditDelOptionsModal").modal({
                backdrop: "static",
                keyboard: false,
            });
        }
    });

    $("body").on("click", ".delUserReseller", function (e) {
        var element = $(this);
        var uid = element.attr("data-uid");
        var utype = element.attr("data-utype");

        var form_element = $("body").find(".delUserResellerForm");
        form_element.attr("action", form_element.attr("data-action") + uid);
        var parent_element = $("body").find("#userEditDelOptionsModal");
        parent_element.find(".action_type").val("delete");

        parent_element.find(".steps_area").removeClass("hide");
        parent_element.find(".just_confirm").addClass("hide");
        parent_element
            .find(".closeEditDelOptionsModal,.submitEditDelUserOptions")
            .removeClass("hide");

        parent_element.find(".change_type").val(utype);

        if (utype == "Reseller") {
            parent_element.find("#del_all").prop("checked", true);
        } else if (utype == "User") {
            parent_element.find(".steps_area").addClass("hide");
            parent_element.find(".just_confirm").removeClass("hide");
        }

        $("body").find("#userEditDelOptionsModal").modal({
            backdrop: "static",
            keyboard: false,
        });
    });
});

$(document).ready(function () {
    $(document).on("keydown", ".ip-url-space", function (event) {
        const key = event.key;
        console.log("Pressed key:", key);

        if (key === " ") {
            event.preventDefault();
            return false;
        }

        if (
            event.ctrlKey ||
            event.metaKey ||
            key === "Backspace" ||
            key === "ArrowLeft" ||
            key === "ArrowRight" ||
            key === "Delete" ||
            key === "Tab" ||
            key === "Enter"
        ) {
            return;
        }

        const allowed = /^[a-zA-Z0-9.]$/;
        if (!allowed.test(key)) {
            event.preventDefault();
            return false;
        }
    });
    $(document).on("paste", ".ip-url-space", function (event) {
        const pastedData = event.originalEvent.clipboardData.getData("text");
        const allowed = /^[a-zA-Z0-9.]*$/;

        if (!allowed.test(pastedData)) {
            event.preventDefault();
            alert("Pasted content contains invalid characters.");
        }
    });

    // Delegated event binding
    $(document).on("keydown", ".text-array-space", validateTextArrayInput);

    function validateTextArrayInput(event) {
        const key = event.key;
        console.log("Pressed key in text-array-space:", key);
        // Block space
        if (key === " ") {
            event.preventDefault();
            return;
        }
       const allowed = /^[0-9A-Fa-f]+$/;

        // Allow control/navigation keys
        if (
            event.ctrlKey ||
            event.metaKey ||
            key === "Backspace" ||
            key === "ArrowLeft" ||
            key === "ArrowRight" ||
            key === "Delete" ||
            key === "Tab" ||
            key === "Enter"
        ) {
            return;
        }

        // Block any key not in the allowed set
        if (!allowed.test(key)) {
            event.preventDefault();
        }
    }
    $(document).on("paste", ".text-array-space", function (event) {
        const pastedData = event.originalEvent.clipboardData.getData("text");
        const allowed = /^[a-zA-Z0-9.,{}]*$/;
        if (event.key === " ") {
            event.preventDefault();
            return;
        }
        if (!allowed.test(pastedData)) {
            event.preventDefault();
            alert("Pasted content contains invalid characters.");
        }
    });
});
