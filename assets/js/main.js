$(document).ready(function () {

    // GetData();

    try {
        DisplayWebsiteSTats();
    } catch (e) {

    }

    DisplayStaffMembers();

    try {
        DisplayStudentTestimonial();
    } catch (e) {

    }

    try {
        DisplayLatestNotifications();
    } catch (e) {

    }
    

    //$('#divShowLatestNotification').html('Loading Please Wait....');
});


function DisplayStudentTestimonial() {
    var Discipline = window.location.pathname;
    //LoadingPopup('Please Wait...', '1');
    $.ajax({
        type: "POST",
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        url: "../../myadmin/webservices.aspx/DisplaStudentTestimonial",
        data: "{Discipline:'" + Discipline+"'}",
        success: function (msg) {


            var dbHTML = msg.d;


            if (dbHTML != "") {

                dbHTML = dbHTML.substring(dbHTML.indexOf('<div class="GetHtmlData">'), dbHTML.indexOf('</form>'));
                dbHTML = dbHTML.replace('<div class="GetHtmlData">', '');
                $('.testimonial-section__slider').html(dbHTML);

                setTimeout(function () {
                    //$('.faculty-section__slider').find('[class*="slick-cloned"]').css('display', 'none');


                    //

                    Launcher();


                }, 500);
            }
            else {

            }

            //setTimeout(function () { LoadingPopup('', '0'); }, 500);
        }
        ,
        error: function (xhr, ajaxOptions, thrownError) {
            // window.location = "Login.aspx";
        }

    });

}


function DisplayWebsiteSTats() {
    $.ajax({
        type: "POST",
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        url: "myadmin/webservices.aspx/WebsiteSTats",
        data: "{}",
        success: function (msg) {
            var dbHTML = msg.d;



            if (dbHTML != "") {

                dbHTML = dbHTML.substring(dbHTML.indexOf('<div class="GetHtmlData">'), dbHTML.indexOf('</form>'));
                dbHTML = dbHTML.replace('<div class="GetHtmlData">', '');
                //$('#divShowLatestNotification').html(dbHTML);
                $('.WebsiteStatusHtml').html(dbHTML);
            }
            else {

            }

            //setTimeout(function () { LoadingPopup('', '0'); }, 500);
        }
        ,
        error: function (xhr, ajaxOptions, thrownError) {
            // window.location = "Login.aspx";
        }

    });

}

function DisplayLatestNotifications() {


    //LoadingPopup('Please Wait...', '1');
    $.ajax({
        type: "POST",
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        url: "myadmin/webservices.aspx/DisplayLatestNotifications",
        data: "{}",
        success: function (msg) {


            var dbHTML = msg.d;


            if (dbHTML != "") {

                dbHTML = dbHTML.substring(dbHTML.indexOf('<div class="GetHtmlData">'), dbHTML.indexOf('</form>'));
                dbHTML = dbHTML.replace('<div class="GetHtmlData">', '');
                //$('#divShowLatestNotification').html(dbHTML);
                $('.home-noti-slider').html(dbHTML);

                setTimeout(function () {
                    //$('.faculty-section__slider').find('[class*="slick-cloned"]').css('display', 'none');


                    //

                    Launcher();


                }, 500);
            }
            else {
                
            }

            //setTimeout(function () { LoadingPopup('', '0'); }, 500);
        }
        ,
        error: function (xhr, ajaxOptions, thrownError) {
            // window.location = "Login.aspx";
        }

    });

}

function DisplayStaffMembers() {

    //$('#divShowStaffMembers').html('Loading Please Wait....');
    var Discipline = window.location.pathname;
    //LoadingPopup('Please Wait...', '1');
    $.ajax({
        type: "POST",
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        url: "../../myadmin/webservices.aspx/DisplayStaffMembers",
        data: "{Discipline:'" + Discipline + "'}",
        success: function (msg) {


            var dbHTML = msg.d;


            if (dbHTML != "") {

                dbHTML = dbHTML.substring(dbHTML.indexOf('<div class="GetHtmlData">'), dbHTML.indexOf('</form>'));
                dbHTML = dbHTML.replace('<div class="GetHtmlData">', '');

                

                $('.faculty-section__slider').html(dbHTML);

                //setTimeout(function () {
                //    $('.faculty-section__slider').find('.slick-cloned').remove();
                //   // alert('fdsfdf');
                //}, 1000);


                setTimeout(function () {
                    //$('.faculty-section__slider').find('[class*="slick-cloned"]').css('display', 'none');
                  

                    //

                    Launcher();
                    

                }, 500);



            }
            else {
                //  $('#divShowStaffMembers').html('');
            }

            //setTimeout(function () { LoadingPopup('', '0'); }, 500);
        }
        ,
        error: function (xhr, ajaxOptions, thrownError) {
            // window.location = "Login.aspx";
        }

    });
}


function Launcher() {


    $('.top-notification-slider').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        speed: 300,
        autoplay: true,
        autoplaySpeed: 2000,
        slidesToShow: 1,
        slidesToScroll: 1,
        pauseOnHover: true,
        nextArrow: '<div class="fa fa-angle-double-right slick-next"></div>',
        prevArrow: '<div class="fa fa-angle-double-left slick-prev"></div>',
    });


    $('.home-noti-slider').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 300,
        autoplay: true,
        autoplaySpeed: 2000,
        slidesToShow: 1,
        slidesToScroll: 1,
        variableWidth: true,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });

    $('.specialization-slider').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 300,
        autoplay: true,
        autoplaySpeed: 2000,
        slidesToShow: 1,
        slidesToScroll: 1,
        variableWidth: true,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });

    $('.placementLogos-section__slider').not('.slick-initialized').slick({
        dots: false,
        arrows: false,
        infinite: true,
        speed: 300,
        autoplay: true,
        autoplaySpeed: 2000,
        slidesToShow: 1,
        slidesToScroll: 1,
        variableWidth: true,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });





    $('.testimonial-section__slider').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        variableWidth: true,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });



    $('.faculty-section__slider').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        variableWidth: true,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });

$('.faculty-section__sliderLib').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        variableWidth: true,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });



    $('.club-listings-slider').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });


    $('.researchThumbs-slider').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });



    $('.testimoni-section__slider').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 2,
        slidesToScroll: 1,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',

        responsive: [{
            breakpoint: 768,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }]
    });


    $('.accredation-section__slider').not('.slick-initialized').slick({
        dots: false,
        arrows: false,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToScroll: 1,
        variableWidth: true,

        responsive: [{
            breakpoint: 768,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }]
    });

    $('.ssv-thumb2').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 3,
        slidesToScroll: 3,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',

        responsive: [{
            breakpoint: 576,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                variableWidth: true,
                arrows: 0,
            }
        }]

    });

    $('.aboutImage-slider').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 2,
        slidesToScroll: 1,
        arrows: false,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',

        responsive: [{
            breakpoint: 576,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }]
    });


    $('.timeline__slider').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        variableWidth: true,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });


    $('.slider-for').not('.slick-initialized').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        dots: false,
        infinite: true,
        asNavFor: '.slider-nav',
        nextArrow: '<div class="fa fa-angle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-angle-left slick-prev"></div>',
        responsive: [{
            breakpoint: 992,
            settings: {
                arrows: true,
            }
        }]
    });
    
    $('.slider-nav').not('.slick-initialized').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        asNavFor: '.slider-for',
        dots: false,
        infinite: true,
        centerMode: true,
        focusOnSelect: true,
        nextArrow: '<div class="fa fa-angle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-angle-left slick-prev"></div>',
        responsive: [{
            breakpoint: 992,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
                centerMode: false
            }
        },
        {
            breakpoint: 576,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
                centerMode: false
            }
        }
        ]

    });


    $('.placedStudents--block__slider').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        arrows: false,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 3,
        slidesToScroll: 1,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',

        responsive: [{
            breakpoint: 768,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
            }
        },
        {
            breakpoint: 576,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }
        ]

    });



    $('.admission-banner__notification--slider').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
    });


    $('.admissionPrograms-section__slider').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 4,
        slidesToScroll: 1,
        variableWidth: true,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',

        responsive: [{
            breakpoint: 1600,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
            }
        },
        {
            breakpoint: 1365,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
            }
        },
        {
            breakpoint: 992,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
            }
        }
        ]
    });



    $('.as-details').not('.slick-initialized').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        dots: false,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 4000,
        asNavFor: '.as-nav',
        nextArrow: '<div class="fa fa-angle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-angle-left slick-prev"></div>',
        responsive: [{
            breakpoint: 992,
            settings: {
                arrows: true,
            }
        }]
    });
    $('.as-nav').not('.slick-initialized').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        asNavFor: '.as-details',
        dots: false,
        infinite: true,
        focusOnSelect: true,
        arrows: false,
        nextArrow: '<div class="fa fa-angle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-angle-left slick-prev"></div>',
        responsive: [{
            breakpoint: 992,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
                centerMode: false
            }
        }]

    });


    $('.reseacrhStory__slider').not('.slick-initialized').slick({
        dots: true,
        infinite: true,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 3,
        slidesToScroll: 3,
        arrows: false,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',

        responsive: [{
            breakpoint: 992,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 1
            }
        },
        {
            breakpoint: 576,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1,
            }
        }
        ]
    });



    $('.infra-slider').not('.slick-initialized').slick({
        dots: false,
        infinite: true,
        arrows: false,
        speed: 1200,
        autoplay: true,
        autoplaySpeed: 4000,
        slidesToShow: 1,
        slidesToScroll: 1,
        nextArrow: '<div class="fa fa-arrow-circle-right slick-next"></div>',
        prevArrow: '<div class="fa fa-arrow-circle-left slick-prev"></div>',
    });





    $('.breadcrumb li a').text(function () {
        return $(this).text().replace(/.php/g, '');
    });

    $(".menu-programs-list li").on('mouseenter', function () {
        $(this).addClass("active")
            .siblings(this).removeClass("active");
    });

    $(".navbar-nav li").on('click', function () {
        $(this).toggleClass("active")
            .siblings(this).removeClass("active");
    });

    $(".navbar-toggler").click(function () {
        $('.overlay-bg').toggleClass('show');
    });

    $(".overlay-bg").click(function () {
        $(this).removeClass('show');
        $('.offcanvas-collapse').removeClass('open');
    });


    $('.allNotification__list').addClass(window.localStorage.toggled);
    $('.allNotification__icon').on('click', function () {
        if (window.localStorage.toggled != "show") {
            $('.allNotification__list').addClass("show", true);
            window.localStorage.toggled = "show";
        } else {
            $('.allNotification__list').removeClass("show", false);
            window.localStorage.toggled = "";
        }
    });



    $(window).scroll(function () {
        if ($(this).scrollTop() > 1) {
            $('.header').addClass("sticky");
            $('.top-notification-slider').addClass("stickyBottom").slick('slickGoTo', 0);
            $('.allNotification').addClass("moveUp");
        } else {
            $('header').removeClass("sticky");
            $('.top-notification-slider').removeClass("stickyBottom");
            $('.allNotification').removeClass("moveUp");
        }
        if ($(window).scrollTop() >= 500) {
            $('.gotoTop').addClass('moveUp');
        } else {
            $('.gotoTop').removeClass('moveUp');
        }
    });


    $('.gotoTop__icon').click(function () {
        $("html, body").animate({ scrollTop: '0' }, 600);
    });



    (() => {
        'use strict'

        document.querySelector('#navbarSideCollapse').addEventListener('click', () => {
            document.querySelector('.offcanvas-collapse').classList.toggle('open')
        })
    })()


    $('.collapse').on('shown.bs.collapse', function (e) {
        var $card = $(this).closest('.accordion-item');
        var $open = $($(this).data('parent')).find('.collapse.show');
        var additionalOffset = 150;
        if ($card.prevAll().filter($open.closest('.accordion-item')).length !== 0) {
            additionalOffset = $open.height();
        }
        $('html,body').animate({
            scrollTop: $card.offset().top - additionalOffset
        }, 500);
    });


    (() => {
        'use strict'

        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
    })()

    $(".library-sec__exLink li").on('mouseenter', function () {
        $(this).addClass("active")
            .siblings(this).removeClass("active");
    });

    $(".otherFacilities .col-lg-4").on('mouseenter', function () {
        $(this).addClass("active")
            .siblings(this).removeClass("active");
    });


    $(window).ready(function () {
        setInterval(function () {
            $('.rightFixed-btns').addClass("show")
        }, 3000);

    });


    $('.event-video [data-fancybox]').fancybox({
        toolbar: false,
        smallBtn: true,
        arrows: false,
        iframe: {
            preload: false
        }
    })

    let label = document.querySelectorAll(".accordion-list > li")
    label.forEach((e) => {
        e.addEventListener("click", () => {
            removeClass()
            e.classList.toggle("active")
        })
    })

    function removeClass() {
        label.forEach((e) => {
            e.classList.remove("active")
        })
    }



    //darkmode code
    const themeToggle = document.querySelector(
        '.theme-switch input[type="checkbox"]'
    );
    const currentTheme = localStorage.getItem("theme");

    if (currentTheme) {
        document.documentElement.setAttribute("data-theme", currentTheme);
        if (currentTheme === "dark") {
            themeToggle.checked = true;
        }
    }

    function switchTheme(e) {
        if (e.target.checked) {
            document.documentElement.setAttribute("data-theme", "dark");
            localStorage.setItem("theme", "dark");
        } else {
            document.documentElement.setAttribute("data-theme", "light");
            localStorage.setItem("theme", "light");
        }
    }

    themeToggle.addEventListener("change", switchTheme, false);




    let darkMode = localStorage.getItem('darkMode');
    const darkModeToggle = document.querySelector('#checkbox');

    const enableDarkMode = () => {
        document.body.classList.add('darkmode');
        localStorage.setItem('darkMode', 'enabled');
    }
    const disableDarkMode = () => {
        document.body.classList.remove('darkmode');
        localStorage.setItem('darkMode', null);
    }
    if (darkMode === 'enabled') {
        enableDarkMode();
    }
    darkModeToggle.addEventListener('click', () => {
        darkMode = localStorage.getItem('darkMode');
        if (darkMode !== 'enabled') {
            enableDarkMode();
        } else {
            disableDarkMode();
        }
    });


    // .howToApply-steps{}

    // $(".howToApply-steps .carousel-indicators button").on('click', function() {
    //     $(this).toggleClass("active").siblings(this).removeClass("active");
    // });



    $(".howToApply-steps .carousel-indicators button:nth-child(1)").on('click', function () {
        $(this).addClass("active");
        $('.howToApply-steps .carousel-item:nth-child(1)').addClass("active");
        $('.howToApply-steps .carousel-indicators button:nth-child(2), .howToApply-steps .carousel-indicators button:nth-child(3)').removeClass("active");
        $('.howToApply-steps .carousel-item:nth-child(3), .howToApply-steps .carousel-item:nth-child(2)').removeClass("active");
    });
    $(".howToApply-steps .carousel-indicators button:nth-child(2)").on('click', function () {
        $(this).addClass("active")
        $('.howToApply-steps .carousel-item:nth-child(2)').addClass("active");
        $('.howToApply-steps .carousel-indicators button:nth-child(1), .howToApply-steps .carousel-indicators button:nth-child(3)').removeClass("active");
        $('.howToApply-steps .carousel-item:nth-child(1), .howToApply-steps .carousel-item:nth-child(3)').removeClass("active");
    });
    $(".howToApply-steps .carousel-indicators button:nth-child(3)").on('click', function () {
        $(this).addClass("active")
        $('.howToApply-steps .carousel-item:nth-child(3)').addClass("active");
        $('.howToApply-steps .carousel-indicators button:nth-child(1), .howToApply-steps .carousel-indicators button:nth-child(2)').removeClass("active");
        $('.howToApply-steps .carousel-item:nth-child(1), .howToApply-steps .carousel-item:nth-child(2)').removeClass("active");
    });


    $(".c-popup").on('click', function () {
        $('.modal').addClass("show").css('display', 'block');
        $('body').addClass("modal-open").css('overflow', 'hidden', 'padding-right', '17px');
    });

    $(".btn-close").on('click', function () {
        $('.modal').removeClass("show").css('display', 'none');
        $('body').removeClass("modal-open").css('overflow', 'inherit', 'padding-right', '0');
    });

    $(".helpAcco .accordion-item").on('click', function () {
        $(this).addClass("active")
            .siblings(this).removeClass("active");
    });


}