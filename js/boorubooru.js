// event pada saat di klik 
$('.page-scroll').on('click', function(event){

 event.preventDefault();

 //ambil isi href
 var tujuan = $(this).attr('href');

 //tangkap elemen yang bersangkutan
 var elemenTujuan = $(tujuan);

 //scroll
 $('html, body').animate({
  scrollTop: elemenTujuan.offset().top - 50
 }, 1000, 'easeInOutExpo');
 
});