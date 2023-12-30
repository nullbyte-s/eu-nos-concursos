function carregarRodape() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("footer").innerHTML = this.responseText;
        }
    };

    xhttp.open("GET", "includes/footer.html", true);
    xhttp.send();
}