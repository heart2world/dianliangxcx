/**
 * Created by Dell on 2017/8/17.
 */


var fillNumOnly=function (obj) {
    var str = obj.value;

    if (/[^0-9]/g.test(str)) {

        obj.value = str.substr(0, str.length - 1);

    }
}
