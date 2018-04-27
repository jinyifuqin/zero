

window.onload=function(){

    $(".shouse").click(function(){
        $(".rbj").show();
        $(".menuS").slideDown(100);
    })
    $(".rbj").click(function(){
        $(".rbj").hide();
        $(".menuS").slideUp(100);
    })


    $(".appcolumn1").click(function(){
        $(".appcolumn1show").slideDown(500);
    })

}