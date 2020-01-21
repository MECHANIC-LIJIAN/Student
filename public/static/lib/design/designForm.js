    var id = 1;
    function deleteRow(obj){
        console.log("de")
        $(obj).parents("tr").remove();
        id=id-1
    };
    $("#addInput").click(function() {
        var option_id = "option_" + String.fromCharCode(64 + id);
        $("#tableD").append(getInput(option_id));
        addPrimaryKey("字段" + id);
        id++;
    });

    $("#addSelect").click(function() {
        var option_id = "option_" + String.fromCharCode(64 + id);
        $("#tableD").append(getSelect(option_id));
        addPrimaryKey("字段" + id);
        id++;
        $(".confirm").click(function() {
            var eleId = $(this).attr("dataid");
            addOptions(eleId);
        });

        $(".editOptions").click(function() {
            var eleId = $(this).attr("dataid");
            $("textarea[id=" + eleId + "_childs" + "]").removeClass("hidden").addClass("show");
            $("a[id="+eleId+"_confirm]").removeClass("hidden").addClass("show")
            $("a[id="+eleId+"_edit]").removeClass("show").addClass("hidden")
        });
        
    });


    //增加一个input
    function getInput(option_id) {
        var ele;
        ele = "<tr>";
        ele =
            ele +
            '<td><label for="' +
            option_id +
            '" class="col-sm-10 control-label">字段' +
            id +
            "</label></td>";
        ele =
            ele +
            '<td><input type="text" required="true" class="form-control" name="' +
            option_id +
            '"></td>';
        ele =
            ele +
            '<td><label for="' +
            option_id +
            '" class="col-sm-10 control-label">规则</label></td>';
        ele =
            ele +
            '<td><select class="form-control" name="' +
            option_id +
            '_rule"><option value="text"">普通文本</option><option value="phone">手机号</option><option value="email">邮箱</option><option value="number">数字</option>';
        ele = ele + "</select></td>";
        ele = ele + '<td><button onclick="deleteRow(this)" class="btn btn-danger">delEle</button></td>';
        ele = ele + "</tr>";
        return ele;
    }
    //增加一个select
    function getSelect(option_id) {
        var ele;
        ele = "<tr>";
        ele =
            ele +
            '<td><label for="' +
            option_id +
            '" class="col-sm-10 control-label">字段' +
            id +
            "</label></td>";
        ele =
            ele +
            '<td><input type="text" required="true" class="form-control" name="' +
            option_id +
            '"><input type="text" class="hidden" value="required" name="' +
            option_id +
            '_rule"></td>';
        ele =
            ele +
            '<td><select class="form-control" required="true" id="' +
            option_id +
            "_options" +
            '"></select></td>';
        ele =
            ele +
            '<td><textarea required="true" id="' +
            option_id +
            '_childs" class="form-control" rows="5"></textarea>';
        ele =
            ele +
            '<a class="confirm" id="'+option_id+'_confirm" dataid="' +
            option_id +
            '">确认</a><a class="editOptions hidden" id="'+option_id+'_edit" dataid="' +
            option_id +
            '">修改</a></td>';
        ele =
            ele +
            '<td><button class="btn btn-danger" onclick="deleteRow(this)">delEle</button></td>';
        ele = ele + "</tr>";
        return ele;
    }

    function addOptions(eleId) {
        $("select[id=" + eleId + "_options" + "]").empty();
        childs = $("textarea[id=" + eleId + "_childs]")
            .val()
            .split("\n");
        childs = childs.filter(function(item) {
            return item && item.trim();
        });
        if (childs.length == 0) {
            showErrorMsg("该项必填");
        } else {
            var dataDivId = eleId + "_data";

            if ($("div[id=" + dataDivId + "]")[0]) {
                $("div[id=" + dataDivId + "]").empty();
            } else {
                $("#tableD").append("<div id='" + dataDivId + "'></div>");
            }

            for (var m = 0; m < childs.length; m++) {
                childId = eleId + "_" + (m + 1);
                $("select[id=" + eleId + "_options" + "]").append(
                    "<option id='" + childId + "'>" + childs[m] + "</option>"
                );
                $("#" + dataDivId).append(
                    "<input type='text' value='" +
                    childs[m] +
                    "' name='" +
                    childId +
                    "' class='hidden'>"
                );
            }
            $("textarea[id=" + eleId + "_childs" + "]").addClass("hidden");
            $("a[id="+eleId+"_confirm]").addClass("hidden")
            $("a[id="+eleId+"_edit]").removeClass("hidden").addClass("show")
        }
    }

    function addPrimaryKey(field) {
        $("#primaryKey").append(
            "<option value='" + field + "'>" + field + "</option>"
        );
    }
