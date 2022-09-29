/*
let box = document.getElementById('carte_soin'),
    btn = document.getElementById('button_carte_soin');

btn.addEventListener('click', function() {

    if (box.classList.contains('hidden')) {
        box.classList.remove('hidden');
        setTimeout(function() {
            box.style.display = 'block';
            btn.innerHTML = "Ne plus afficher";
            box.classList.remove('visuallyhidden');
        }, 20);
    } else {
        box.classList.add('visuallyhidden');
        box.addEventListener('transitionend', function(e) {
            box.classList.add('hidden');
            box.style.display = 'none';
            btn.innerHTML = "Découvrir les soins";
        }, {
            capture: false,
            once: true,
            passive: false
        });
    }

}, false);


let box1 = document.getElementById('soin1'),
    box2 = document.getElementById('soin2'),
    box3 = document.getElementById('soin3'),
    box4 = document.getElementById('soin4'),
    box5 = document.getElementById('soin5'),
    box6 = document.getElementById('soin6'),
    box7 = document.getElementById('soin7'),
    box8 = document.getElementById('soin8');
box9 = document.getElementById('soin9');

let text2 = box2.innerHTML;
let class2 = box2.classList[2];


box1.addEventListener('mouseover', function() {
    var height2 = box2.clientHeight;
    if (height2 >= box2.clientHeight) {
        box2.style.minHeight = height2 + "px";
    }

    box2.classList.remove(class2);
    box2.style.padding = "50px 0px 0px 0px";
    box2.innerHTML = "<img src=\"build/images/pic-box1.jpg\" alt=\"Main photo créé par valuavitaly - fr.freepik.com\" /> ";
})
box1.addEventListener('mouseout', function() {
    box2.style.padding = "4em 4em 2em 6em";
    box2.classList.add(class2);
    box2.innerHTML = text2;
})

let text1 = box1.innerHTML;
let class1 = box1.classList[2];

box2.addEventListener('mouseover', function() {
    var height1 = box2.clientHeight;
    if (height1 >= box2.clientHeight) {
        box1.style.minHeight = height1 + "px";
    }


    box1.classList.remove(class1);
    box1.style.padding = "50px 0px 0px 0px";
    box1.innerHTML = "<img src=\"build/images/pic-box2.jpg\" alt=\"Main photo créé par Racool_studio - fr.freepik.com\" /> ";
})
box2.addEventListener('mouseout', function() {
    box1.style.padding = "4em 4em 2em 6em";
    box1.classList.add(class1);
    box1.innerHTML = text1;
})


let text3 = box3.innerHTML;
let class3 = box3.classList[2];

box4.addEventListener('mouseover', function() {
    var height3 = box3.clientHeight;
    if (height3 >= box3.clientHeight) {
        box3.style.minHeight = height3 + "px";
    }
    box3.classList.remove(class3);
    box3.style.padding = "50px 0px 0px 0px";
    box3.innerHTML = "<img src=\"build/images/pic-box4.jpg\" alt=\"Main photo créé par senivpetro - fr.freepik.com\" /> ";
})
box4.addEventListener('mouseout', function() {
    box3.style.padding = "4em 4em 2em 6em";
    box3.classList.add(class3);
    box3.innerHTML = text3;
})


let text4 = box4.innerHTML;
let class4 = box4.classList[2];

box3.addEventListener('mouseover', function() {
    var height4 = box4.clientHeight;
    if (height4 >= box4.clientHeight) {
        box4.style.minHeight = height4 + "px";
    }
    box4.classList.remove(class4);
    box4.style.padding = "50px 0px 0px 0px";
    box4.innerHTML = "<img src=\"build/images/pic-box3.jpg\" alt=\"Main photo créé par cookie_studio - fr.freepik.com\" /> ";
})
box3.addEventListener('mouseout', function() {
    box4.style.padding = "4em 4em 2em 6em";
    box4.classList.add(class4);
    box4.innerHTML = text4;
})


let text9 = box9.innerHTML;
let class9 = box9.classList[2];

box5.addEventListener('mouseover', function() {
    box9.classList.remove(class9);
    box9.style.padding = "50px 0px 0px 0px";
    box9.innerHTML = "<img src=\"build/images/pic-box5.jpg\" alt=\"Main photo créé par kroshka__nastya - fr.freepik.com\" /> ";

})
box5.addEventListener('mouseout', function() {
    box9.style.padding = "4em 4em 2em 6em";
    box9.classList.add(class9);
    box9.innerHTML = text9;
})

let text5 = box5.innerHTML;
let class5 = box5.classList[2];

box9.addEventListener('mouseover', function() {
    box5.classList.remove(class5);
    box5.style.padding = "50px 0px 0px 0px";
    box5.innerHTML = "<img src=\"build/images/pic-box9.jpg\" alt=\"Main photo créé par master1305 - fr.freepik.com\" /> ";

})
box9.addEventListener('mouseout', function() {
    box5.style.padding = "4em 4em 2em 6em";
    box5.classList.add(class5);
    box5.innerHTML = text5;
})

let text8 = box8.innerHTML;
let class8 = box8.classList[2];

box7.addEventListener('mouseover', function() {
    var height8 = box8.clientHeight;
    if (height8 >= box8.clientHeight) {
        box8.style.minHeight = height8 + "px";
    }


    box8.classList.remove(class8);
    box8.style.padding = "50px 0px 0px 0px";
    box8.innerHTML = "<img src=\"build/images/pic-box7.jpg\" alt=\"Main photo créé par rawpixel.com - fr.freepik.com\" /> ";

})
box7.addEventListener('mouseout', function() {
    box8.style.padding = "4em 4em 2em 6em";
    box8.classList.add(class8);
    box8.innerHTML = text8;
})

let text7 = box7.innerHTML;
let class7 = box7.classList[2];

box8.addEventListener('mouseover', function() {
    var height7 = box7.clientHeight;
    if (height7 >= box7.clientHeight) {
        box7.style.minHeight = height7 + "px";
    }

    box7.classList.remove(class7);
    box7.style.padding = "50px 0px 0px 0px";
    box7.innerHTML = "<img src=\"build/images/pic-box8.jpg\" alt=\"Main photo créé par Racool_studio - fr.freepik.com\" /> ";

})
box8.addEventListener('mouseout', function() {
    box7.style.padding = "4em 4em 2em 6em";
    box7.classList.add(class7);
    box7.innerHTML = text7;
})
*/
function insertAfter(referenceNode, newNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}



if (window.screen.availWidth < 480) {
    $('#pic2').each(function() {
        $(this).insertAfter($(this).parent().find('#soin2'));
    });
    $('#pic4').each(function() {
        $(this).insertAfter($(this).parent().find('#soin8'));
    });
    $('#pic6').each(function() {
        $(this).insertAfter($(this).parent().find('#soin4'));
    });
    $('#pic8').each(function() {
        $(this).insertAfter($(this).parent().find('#soin5'));
    });
}