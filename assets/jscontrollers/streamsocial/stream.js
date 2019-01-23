var $grid;
$(document).ready(function(){

	gridSizeWidth();

	$grid = $('.grid').isotope({
  	itemSelector: '.grid-item',
	  	masonry: {
	  	  	columnWidth: '.stream-item'
	  	}
	});

	$(window).resize(function(){
		gridSizeWidth();
	});


	function filterBy(name){
		$grid.isotope({ filter: name });
	}
	function gridSizeWidth(){
		let to_div = 4;

		if ($(window).width() <= 597) {
			to_div = 1;
		}else if ($(window).width() <= 991){
			to_div = 2;
		}else if ($(window).width() <= 1200) {
			to_div = 3;
		}
		

		let width_container = ($('.stream-item').parent().width() / to_div);
		$('.stream-item').css({
			width: (width_container - 10) + 'px'
		});
	}



	$('#searchByText').keyup(function(){
		var to_search = $(this).val();
		var patt = new RegExp(to_search);

		$grid.isotope({ filter: function() {
		  	var className = $(this).attr('class');
		  	className = className.split(' ').pop();
		  	var title = $(this).find('.body-title').text().toLowerCase();
		  	var text = $(this).find('.stream-description').find('p').text();

		  	if (to_search.length == 0) {
		  		return true;
		  	}

		  	if (className.search(patt) !== -1 || title.search(patt) !== -1 || text.search(patt) !== -1) {
		  		return true;
		  	}

		  	return false;

		  	//name.match( /ium$/ )
		  
		} })

	});

});


