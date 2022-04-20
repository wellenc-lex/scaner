function changeactive(liname, divname) {
    $('.active').removeClass('active');
    $(liname).addClass('active');
    $('.tab-pane fade').removeClass('active');
    $(divname).addClass('tab-pane fade active in');

    activebox = document.getElementById("activescanbox").checked;
    passivebox = document.getElementById("passivescanbox").checked;
    if (activebox === true || passivebox === true)
        $(".defaultclass").toggle();

}
