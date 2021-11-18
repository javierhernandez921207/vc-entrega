function addcarrito(prod, cant) {
    var Ruta = Routing.generate('pedido_new_ajax');
    console.log(cant)

    $.ajax({
        type: 'POST',
        url: Ruta,
        data: ({prod: prod, cant: cant}),
        async: true,
        dataType: "json",
        success: function (data) {
            if (data['msg'] == 0) {
                toastr["success"]("Producto añadido.");
                ordenarCarrito(data);
            } else
                toastr["error"]("La cantidad de este producto no es válida.");
        },
        error: function () {
            toastr["error"]("error al hacer el pedido");
        }
    });
}

function ordenarCarrito(data) {
    var carrito = data['carrito'];
    var div = document.getElementById('carrito');

    while (div.hasChildNodes())
        div.removeChild(div.firstChild);

    var i;
    var total = 0;
    var cantprod = 0;
    for (i = 0; i < carrito.length; i++) {
        cantprod += parseInt(carrito [i]['cantidad']);
        total += carrito[i]['precio'] * carrito[i]['cantidad'];
        const p = document.createElement("p");
        const a = document.createElement("a");
        const ic = document.createElement("i");
        const rutadel = Routing.generate('pedido_delete_prod', {'id': carrito[i]['id']});
        a.setAttribute('href', rutadel);
        a.setAttribute('style', 'color:#dc3545')
        ic.setAttribute('class', 'fa fa-minus-circle');
        a.appendChild(ic);
        p.appendChild(a);
        div.appendChild(p);
        p.insertAdjacentText('beforeend', " ( " + carrito[i]['cantidad'] + " )  " + carrito[i]['nombre'] + " - " + carrito[i]['precio'].toLocaleString('en-US', {
            style: 'currency',
            currency: 'USD'
        }) + " ");
    }

    var spam = document.getElementById('cantprod');
    console.log(cantprod)
    spam.innerText = cantprod;

    const h5 = document.getElementById('totalpedido');
    const h6 = document.getElementById('totalcup');
    h5.innerText = "Total: " + total.toLocaleString('en-US', {style: 'currency', currency: 'USD'});
    h6.innerText = '(' + (total * data['cambio']).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD'
    }) + ' cup)';
}

toastr.options = {
    "closeButton": true,
    "debug": true,
    "newestOnTop": false,
    "progressBar": true,
    "positionClass": "toast-top-center",
    "preventDuplicates": false,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "3000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
}

