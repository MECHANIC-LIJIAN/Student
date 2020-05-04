  function IsPC() {
    var userAgentInfo = navigator.userAgent;
    var Agents = [
      "Android",
      "iPhone",
      "SymbianOS",
      "Windows Phone",
      "iPad",
      "iPod",
    ];
    var flag = true;
    for (var v = 0; v < Agents.length; v++) {
      if (userAgentInfo.indexOf(Agents[v]) > 0) {
        flag = false;
        break;
      }
    }
    return flag;
  }
  var isPC = IsPC();
  if (isPC) {
    layer = layer;
    function showMsg(msg) {
      layer.msg(msg, {
        icon: 6,
        time: 2000,
      });
    }

    function showMsgAndHref(data) {
      layer.msg(
        data.msg,
        {
          icon: 6,
          time: 2000,
        },
        function () {
          location.href = data.url;
        }
      );
    }

    function showErrorMsg(msg) {
      layer.open({
        title: "错误！",
        content: msg,
        icon: 5,
        anim: 5,
      });
    }

    function confirm(msg, title, url, data) {
      layer.confirm(
        msg,
        {
          title: title,
          icon: 3,
        },
        function (index) {
          layer.close(index);
          $.ajax({
            url: url,
            type: "post",
            data: data,
            dataType: "json",
            success: function (data) {
              if (data.code == 1) {
                showMsgAndHref(data);
              } else {
                showErrorMsg(data.msg);
              }
            },
          });
        }
      );
    }
  } else {
    layer = mobile;
    function showMsg(msg) {
      //提示
      layer.open({
        content: msg,
        skin: "msg",
        time: 2, //2秒后自动关闭
      });
    }

    function showMsgAndHref(data) {
      layer.open({
        content: data.msg,
        btn: "我知道了",
        end: function (index) {
          setTimeout(function () {
            // $.mobile.changePage(data.url); //手机网页式跳转
            location.href = data.url;
          }, 1000);
        },
      });
    }
    function showErrorMsg(msg) {
      layer.open({
        content: msg,
        btn: "我知道了",
      });
    }

    //询问框
    function confirm(msg, title, url, data) {
      layer.open({
        content: msg,
        btn: ["确认", "取消"],
        yes: function (index) {
          $.ajax({
            url: url,
            type: "post",
            data: data,
            success: function (data) {
              if (data.code == 1) {
                showMsgAndHref(data);
              } else {
                showErrorMsg(data.msg);
              }
            },
          });
        },
      });
    }
  }