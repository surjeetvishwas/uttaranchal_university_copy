  $(function() {
 $("#navbarSideCollapse").on("click", function(e) {
    $(".navbar-collapse").addClass("open");
   $(".overlay-bg").addClass("show");
    e.stopPropagation()
  });


 $(".show").on("click", function(e) {
    $(".navbar-collapse").removeClass("open");
    e.stopPropagation()
  }); 

 }); 
 


$('.dd-menu').click( function(){
    if ( $(this).hasClass('active') ) {
        $(this).removeClass('active');
    } else {
        $('.dd-menu').removeClass('active');
        $(this).addClass('active');    
    }
});