function loadSearchResults() {
    var usuario = localStorage.getItem('usuario');

    $.ajax({
        url: 'backend/view_search.php',
        type: 'POST',
        dataType: 'json',
        data: JSON.stringify({ usuario }),
        success: function (data) {
            const results = data.data;

            if (results && results.length > 0) {
                const table = $('#searchResults').DataTable({
                    data: results,
                    language: {
                        "sEmptyTable": "Nenhum dado encontrado",
                        "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                        "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                        "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                        "sInfoPostFix": "",
                        "sInfoThousands": ".",
                        "sLengthMenu": "_MENU_ resultados por página",
                        "sLoadingRecords": "Carregando...",
                        "sProcessing": "Processando...",
                        "sZeroRecords": "Nenhum registro encontrado",
                        "sSearch": "Pesquisar",
                        "oPaginate": {
                            "sNext": "Próximo",
                            "sPrevious": "Anterior",
                            "sFirst": "Primeiro",
                            "sLast": "Último"
                        },
                        "oAria": {
                            "sSortAscending": ": Ordenar colunas de forma ascendente",
                            "sSortDescending": ": Ordenar colunas de forma descendente"
                        }
                    },
                    columns: [
                        { title: 'Título', data: 'titulo' },
                        { title: 'Página', data: 'pagina' }
                    ]
                });
            }
        },
        error: function (error) {
            console.error('Erro na solicitação:', error);
        }
    });
}