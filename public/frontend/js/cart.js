function addtocart(url)
{
   event.preventDefault();

   $.ajax({
      url: url,
      type: 'get',
      dataType: 'JSON',
      success: function (data) {
         console.log(data);
         $("#newrefresh").load("/"+" #newrefresh>*","");
      }
   });
}