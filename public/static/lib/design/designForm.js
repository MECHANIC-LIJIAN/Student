var formType = $("#formType").val();
if (formType === "word") {
  var id = 2;
} else {
  var id = 1;
}
$("#addInput").click(function () {
  $(".deleteRow").addClass("hidden");

  var option_id = "option_" + id;

  $("#tableD").append(getInput(option_id));

  addPrimaryKey(option_id, "字段" + id);

  // $(".input-type").change(function () {
  //   if($(this).val()==="text"){
  //     option_id=$(this).attr("dataid");
  //     delPrimaryKey(option_id);
  //   }
  // });

  id++;
});

$("#addSelect").click(function () {
  $(".deleteRow").addClass("hidden");
  var option_id = "option_" + id;
  $("#tableD").append(getSelect(option_id));
  id++;

  $(".confirm").click(function () {
    var eleId = $(this).attr("dataid");
    addOptions(eleId);
  });

  $(".editOptions").click(function () {
    var eleId = $(this).attr("dataid");
    $("textarea[id=" + eleId + "_childs" + "]")
      .removeClass("hidden")
      .addClass("show");
    $("a[id=" + eleId + "_confirm]")
      .removeClass("hidden")
      .addClass("show");
    $("a[id=" + eleId + "_edit]")
      .removeClass("show")
      .addClass("hidden");
  });
});

//拼接一个input的内容
function getInput(option_id) {
  var ele;
  ele = "<tr>";
  ele =
    ele +
    '<td><label for="' +
    option_id +
    '" class="control-label">字段' +
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
    '" class="control-label">规则</label></td>';
  ele =
    ele +
    '<td><select class="form-control input-type" dataid=' +
    option_id +
    ' name="' +
    option_id +
    '_rule">\
    <option value="text|required"">普通文本</option>\
    <option value="phone|required">手机号</option>\
    <option value="email|required">邮箱</option>\
    <option value="number|required">数字</option>\
    <option value="text">非必填项</option>';
  ele = ele + "</select></td>";

  if (formType === "word") {
    ele =
      ele +
      '<td><input type="button" value="复制到word" class="btn btn-success" onclick=copyToClip('+
        '"${' +
        option_id +
        '}") /></td>'
  }

  ele =
    ele +
    '<td><a dataid="' +
    option_id +
    '" onclick="deleteRow(this)" class="deleteRow">删除</a></td>';

  ele = ele + "</tr>";

  return ele;
}

//拼接一个select的内容
function getSelect(option_id) {
  var ele;
  ele = "<tr>";
  ele =
    ele +
    '<td><label for="' +
    option_id +
    '" class="control-label">字段' +
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
    '_childs" class="form-control" rows="4" cols="3" placeholder="一行一个选项"></textarea>';
  ele =
    ele +
    '<a class="confirm" id="' +
    option_id +
    '_confirm" dataid="' +
    option_id +
    '">确认</a><a class="editOptions hidden" id="' +
    option_id +
    '_edit" dataid="' +
    option_id +
    '">修改</a></td>';

  if (formType === "word") {
    ele =
      ele +
      '<td><input type="button" value="复制到word" class="btn btn-success" onclick=copyToClip('+
        '"${' +
        option_id +
        '}") /></td>'
  }

  ele =
    ele +
    '<td><a dataid="' +
    option_id +
    '" onclick="deleteRow(this)" class="deleteRow">删除</a></td>';
  ele = ele + "</tr>";
  return ele;
}

//删除行事件
function deleteRow(obj) {
  $(obj).parents("tr").remove();
  $("a.deleteRow").last().removeClass("hidden").addClass("show");
  var option_id = $(obj).attr("dataid");
  $("#primaryKey option[value='" + option_id + "']").remove();
  id = id - 1;
}

function addOptions(eleId) {
  $("select[id=" + eleId + "_options" + "]").empty();
  childs = $("textarea[id=" + eleId + "_childs]")
    .val()
    .split("\n");
  childs = childs.filter(function (item) {
    return item && item.trim();
  });
  childs = uniq(childs);
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
    $("a[id=" + eleId + "_confirm]").addClass("hidden");
    $("a[id=" + eleId + "_edit]")
      .removeClass("hidden")
      .addClass("show");
  }
}

function addPrimaryKey(option_id, field) {
  $("#primaryKey").append(
    "<option value='" + option_id + "'>" + field + "</option>"
  );
}

function delPrimaryKey(option_id) {
  $("#primaryKey option[value='" + option_id + "']").remove();
}

// 思路：获取没重复的最右一值放入新数组
/*
 * 推荐的方法
 *
 * 方法的实现代码相当酷炫，
 * 实现思路：获取没重复的最右一值放入新数组。
 * （检测到有重复值时终止当前循环同时进入顶层循环的下一轮判断）*/
function uniq(array) {
  var temp = [];
  var index = [];
  var l = array.length;
  for (var i = 0; i < l; i++) {
    for (var j = i + 1; j < l; j++) {
      if (array[i] === array[j]) {
        i++;
        j = i;
      }
    }
    temp.push(array[i]);
    index.push(i);
  }
  return temp;
}
