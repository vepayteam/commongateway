"use strict";

$(document).ready(function () {

    $('body').append('<div id="ajax_loader"></div>');

    $("#topMain > li > a").on('click',function () {
        var elementClick = $(this).attr("href");
        var destination = $(elementClick).offset().top - window._headHeight - 30;
        $('html, body').animate({scrollTop: destination}, 1100);
        return false;
    });
    $(window).scroll(function () {
        if ($("#topMain > li > a").length > 0) {
            $("#topMain > li > a").each(function () {
                var window_top = $(window).scrollTop();
                var elementClick = $(this).attr("href");
                if (elementClick.indexOf('#') === 0) {
                    var div_top = $(elementClick).offset().top;

                    if (window_top > div_top - window._headHeight - 80) {
                        $(this).parent('li').addClass('active');
                        $(this).parent('li').siblings().removeClass('active');
                    } else {
                        $(this).parent('li').removeClass('active');
                    }
                }
            });
        }
        if ($(window).scrollTop() > 50) {
            $('.contact-modal').removeClass('open');
        }
    });

    $('.contact-modal__name').on('click', function () {
        $('.contact-modal').toggleClass('open');
    });

    _parallaxNew();
    /*_topNavCalibrateNew();
    // recalibrate menu (mobile = slim mode) on resize
    $(window).resize(function() {
        _topNavCalibrateNew();
    });*/

    $('[data-target=".ajax_modal_container"]').on('click', function () {
        var $this = $(this);
        $.ajax({
            type: "GET",
            url: $this.attr('href'),
            success: function (data) {
                $(".ajax_modal_container .modal-content").html(data);
            }
        });
    });


    // отправка форм обратной связи
    /*$('body').on('submit', '.send_common' , function(){
        var $this = $(this);
        if ($this.find('input[name=form_type]').length < 1){
            return true;
        }
        if ($this.find('input:file').length > 0){
            var formData = new FormData();

            $this.find('input:file').each(function(index, elem){
                formData.append(elem.name, elem.files[0]);
            });

            $this.find('input, textarea, select').each(function(index, elem){
                formData.append(elem.name, $(elem).val());
            });
            formData.append('send', '1');

            $.ajax({
                type: 'post',
                data:  formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                url: 'ajax.php?op=send_common',
                beforeSend: function(){$('#ajax_loader').show();},
                success: function(data){
                    $this.parent('.form_wrapper').replaceWith(data.html);
                },
                complete: function(){$('#ajax_loader').hide();}
            });
        } else {
            $.ajax({
                type: 'post',
                data: $this.serialize()+'&send=1',
                dataType: 'json',
                url: 'ajax.php?op=send_common',
                beforeSend: function(){$('#ajax_loader').show();},
                success: function(data){
                    $this.parent('.form_wrapper').replaceWith(data.html);
                },
                complete: function(){$('#ajax_loader').hide();}
            });
        }
        return false;
    });*/

    $(window).on('load', function () {
        var $preloader = $('#p_prldr');
        $preloader.delay(1000).fadeOut('slow');
    });

    //окно по центру + скролл второй скрыть
    $('.modal').on('show.bs.modal', function () {
        var fh = $("#contactwindow").attr("wndh"); //размер окна
        if (!fh || fh === undefined) {
            fh = 650;
        } else {
            $("#contactwindow").removeAttr("wndh");
            fh = parseInt(fh);
        }
        var wh = $(window).height();
        if (wh > fh + 30) {
            $(this).css('padding-top', (wh - fh) / 2 - 40);
        } else {
            $(this).css('padding-top', 0);
        }

        //$('html').css('overflow-y', 'hidden');
        $('.top-b').css('padding-right', measureScrollbar());

    }).on('hidden.bs.modal', function () {
        //$('html').css('overflow-y', 'auto');
        $('.top-b').css('padding-right', 0);
    });

    function measureScrollbar() { // thx walsh
        var bodyIsOverflowing = document.body.clientWidth < window.innerWidth;
        var scrollDiv = document.createElement('div');
        scrollDiv.className = 'modal-scrollbar-measure';
        var body = $(document.body);
        body.append(scrollDiv);
        var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
        body[0].removeChild(scrollDiv);
        return bodyIsOverflowing ? scrollbarWidth : 0;
    }
});

function _topNavCalibrateNew() {

    window._headHeight = $(".top-bar").outerHeight();

    $('#wrapper').css({"padding-top": window._headHeight + "px"});
    $('.top-bar').removeClass('fixed').addClass('fixed');

    if ($('#header_shadow').length > 0) {
        $('#header_shadow').css({"top": window._headHeight + "px"});
    }
}

function _parallaxNew() {

    if (typeof($.stellar) == "undefined") {

        $(".js_parallax").addClass("parallax-init");
        return false;

    }

    $(window).on('load',function () {

        if ($(".js_parallax").length > 0) {

            if (!Modernizr.touch) {

                $(window).stellar({

                    responsive: true,
                    scrollProperty: 'scroll',
                    parallaxElements: false,
                    horizontalScrolling: false,
                    horizontalOffset: 0,
                    verticalOffset: 0
                });

            } else {

                $(".js_parallax").addClass("disabled");

            }
        }

        $(".js_parallax").addClass("parallax-init");

        // responsive
        $(window).afterResize(function () {
            $.stellar('refresh');
        });

        /*$("#").afterResize(function () {
            $.stellar('refresh');
        });*/

    });
}

/*function contactManyMap() {

	for(var j=0; j < google_map.length; j++){

		var latLang = new google.maps.LatLng(manyCoords[j][0][0], manyCoords[j][0][1]);

		var mapOptions = {
			zoom: googlemap_zoom[j],
			center: latLang,
			disableDefaultUI: false,
			navigationControl: false,
			mapTypeControl: false,
			scrollwheel: true,
			// styles: styles,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		var map = new google.maps.Map(document.getElementById(google_map[j]), mapOptions);

		google.maps.event.trigger(map, 'resize'); map.setZoom( map.getZoom() );

		for(i=0; i<manyCoords[j].length; i++){
			var pos = new google.maps.LatLng(manyCoords[j][i][0], manyCoords[j][i][1]);
			marker = new google.maps.Marker({
				icon: 'data:image/png;base64,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',
				position: pos,
				map: map,
				title: manyTitles[j][i]
			});
			marker.setMap(map);
			google.maps.event.addListener(marker, "click", function() {
				// Add optionally an action for when the marker is clicked
			});
		}

		// kepp googlemap responsive - center on resize
		google.maps.event.addDomListener(window, 'resize', function() {
			map.setCenter(latLang);
		});

	}
}

function showManyMap(initWhat) {
	var script 		= document.createElement('script');
	script.type 	= 'text/javascript';
	script.src 		= '//maps.google.com/maps/api/js?key=AIzaSyCqCn84CgZN6o1Xc3P4dM657HIxkX3jzPY&callback='+initWhat;
	document.body.appendChild(script);
}

var add_google_script = false;

if(typeof(google_map) !== 'undefined' && google_map.length > 0){
	for(i=0; i < google_map.length; i++){
		if($('#'+google_map[i]).length > 0 && add_google_script === false) {
			showManyMap('contactManyMap');
			add_google_script = true;
			break;
		}
	}
}*/
