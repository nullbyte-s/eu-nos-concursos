function carregarPagina(pagina) {
	return new Promise((resolve, reject) => {
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function () {
			if (this.readyState == 4) {
				if (this.status == 200) {
					document.getElementById("content").innerHTML = this.responseText;
					window.scrollTo(0, 0);
					resolve();
				} else {
					reject(new Error(`Erro ao carregar a página: ${pagina}`));
				}
			}
		};

		xhttp.open("GET", "includes/" + pagina, true);
		xhttp.send();
	});
}
