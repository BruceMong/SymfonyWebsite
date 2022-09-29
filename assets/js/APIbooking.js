/*
var weekday = new Array(7);
weekday[0] = "Lundi";
weekday[1] = "Mardi";
weekday[2] = "Mercredi";
weekday[3] = "Wednesday";
weekday[4] = "Thursday";
weekday[5] = "Friday";
weekday[6] = "Saturday";


alert(date.toLocaleDateString("fr-FR", options));
var options = { weekday: "long", year: "numeric", month: "long", day: "2-digit" };
var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
var last = first + 6; // last day is the first day + 6

var firstday = new Date(curr.setDate(first)).toUTCString();
var lastday = new Date(curr.setDate(last)).toUTCString();
alert(firstday.toLocaleDateString("fr-FR", options));
alert(lastday.toLocaleDateString("fr-FR", options));

var curr = new Date; // get current date

var n = weekday[curr.getDay()];
document.getElementById("week").innerHTML = n;
*/
var date = new Date();
var options = { weekday: "long", year: "numeric", month: "long", day: "2-digit" };
var fullDate = date.toLocaleDateString("fr-FR", options);

//document.getElementById("week").innerHTML = fullDate;

var options = { weekday: "long" };
var jour = date.toLocaleDateString("fr-FR", options);
//console.log(jour);
//document.getElementById(jour).style.color = "red";







function getMonday(d) {
    d = new Date(d);
    var day = d.getDay(),
        diff = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is sunday
    d = new Date(d.setDate(diff));
    month = d.getMonth()
    return diff + "/" + month;
}



function getSunday(d) {
    d = new Date(d);
    var day = d.getDay(),
        diff = d.getDate() - day + (day == 0 ? 7 : 7); // adjust when day is sunday
    d = new Date(d.setDate(diff));

    day = d.getDay()
    month = d.getMonth()
    return day + "/" + month;
}

function getNextDayOfWeek(date, dayOfWeek) {
    // Code to check that date and dayOfWeek are valid left as an exercise ;)

    var resultDate = new Date(date.getTime());

    resultDate.setDate(date.getDate() + (7 + dayOfWeek - date.getDay()) % 7);

    return resultDate;
}
MondayDate = getMonday(new Date());
SundayDate = getSunday(new Date());

//document.getElementById("week").innerHTML = "semaine du " + MondayDate + " au " + SundayDate;

//console.log(date.getDay());
//console.log(date.getDate());
//console.log(date.getDate() + date.getDay());


function getLastWeek() {
    var today = new Date();
    var lastWeek = new Date(today.getFullYear(), today.getMonth(), today.getDate() - 7);
    return lastWeek;
}

function getNextweek() {
    var today = new Date();
    var nextweek = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 7);
    return nextweek;
}

var lastWeek = getLastWeek();
var lastWeekMonth = lastWeek.getMonth() + 1;
var lastWeekDay = lastWeek.getDate();
var lastWeekYear = lastWeek.getFullYear();

var lastWeekDisplay = lastWeekMonth + "/" + lastWeekDay + "/" + lastWeekYear;

//console.log(getNextweek());

var lastWeekDisplayPadded = ("00" + lastWeekMonth.toString()).slice(-2) + "/" + ("00" + lastWeekDay.toString()).slice(-2) + "/" + ("0000" + lastWeekYear.toString()).slice(-4);
//console.log(lastWeekDisplay);
//console.log(lastWeekDisplayPadded);


function setDayOld(date, dayOfWeek) {
    date = new Date(date.getTime());
    date.setDate(date.getDate() + (dayOfWeek + 7 - date.getDay()) % 7);
    return date;
}






//--------------------

var moisLun;
var moisDim;

function getMondayOld(d) {
    d = new Date(d);
    var day = d.getDay(),
        diff = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is sunday
    return new Date(d.setDate(diff));
}

function setDay(date, dayOfWeek) {
    date = new Date(date.getTime());
    date.setDate(date.getDate() + (dayOfWeek + 7 - date.getDay()) % 7);
    return date.getDate();
}


var i = 0;
var lundi = getMondayOld(date);
/*
while (i <= 6) {

    var r = setDay(lundi, i);

    switch (i) {
        case 1:
            document.getElementById('lundi').innerHTML += r;
            break;
        case 2:
            document.getElementById('mardi').innerHTML += r;
            break;
        case 3:
            document.getElementById('mercredi').innerHTML += r;
            break;
        case 4:
            document.getElementById('jeudi').innerHTML += r;
            break;
        case 5:
            document.getElementById('vendredi').innerHTML += r;
            break;
    }
    i++;
}
*/
moisLun = lundi;
moisDim = setDayOld(lundi, 6);

var options = { month: "long" };
moisLun = moisLun.toLocaleDateString("fr-FR", options);
moisDim = moisDim.toLocaleDateString("fr-FR", options);


//document.getElementById('nextWeek').href += date.getTime();
//document.getElementById('lastWeek').href += date.getTime();
/*
if (moisLun == moisDim)
    document.getElementById("week").innerHTML = moisLun;
else
    document.getElementById("week").innerHTML = moisLun + " - " + moisDim;
  */
//g fait en php finalement