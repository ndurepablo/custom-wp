const hoods = {
    "caba": {
        "label": "Ciudad autónoma de Buenos Aires",
        "hoods": {
            "1": {
                "name": "Palermo",
                "slug": "hood_palermo",
                "id": "datepicker-palermo",
                "calculateDisabledDate": 1,
                "rangeDays": 10,
                "days": [
                    0,
                    4,
                    5
                ],
                "shippingCost": 900,
                "freeShippingThreshold": 4000
            },
            "2": {
                "name": "Caballito",
                "slug": "hood_caballito",
                "id": "datepicker-caballito",
                "calculateDisabledDate": 1,
                "rangeDays": 5,
                "days": [
                    1,
                    2,
                    3
                ],
                "shippingCost": 800,
                "freeShippingThreshold": 4000
            },
            "3": {
                "name": "Almagro",
                "slug": "hood_almagro",
                "id": "datepicker-almagro",
                "calculateDisabledDate": 1,
                "rangeDays": 7,
                "days": [
                    0,
                    3,
                    5
                ],
                "shippingCost": 1200,
                "freeShippingThreshold": 3000
            }
        }
    },
    "zona_sur": {
        "label": "Zona Sur",
        "hoods": {
            "1": {
                "name": "Wilde",
                "slug": "hood_wilde",
                "id": "datepicker-palermo",
                "calculateDisabledDate": 1,
                "rangeDays": 5,
                "days": [
                    1,
                    3,
                    5
                ],
                "shippingCost": 1800,
                "freeShippingThreshold": 7000
            },
            "2": {
                "name": "Avellaneda",
                "slug": "hood_avellaneda",
                "id": "datepicker-caballito",
                "calculateDisabledDate": 1,
                "rangeDays": 5,
                "days": [
                    1,
                    2,
                    3
                ],
                "shippingCost": 1800,
                "freeShippingThreshold": 7000
            }
        }
    }
};

jQuery(document).ready(function($){
    $(document.body).on('change', 'input[name="radio_region"]', function(){
        // INSTANCIA DE ELIMINACION DE ELEMENTOS
        $('#gba_zones').remove();
        $('#custom_shipping_cost').remove();

        // DECLARACION DE ELEMENTOS NECESARIOS
        let radioButton = $(this).val();
        let customCheckoutField = $('#custom_checkout_field');

        // DOMICIOLIO EN CAPITAL Y GBA SELECCIONADO
        if (radioButton == 'gba') {

            // CREA EL SELECT CON LOS BARRIOS COMO OPTIONS
            let gbaSelect = $('<select>', {id: 'gba_zones'});
            customCheckoutField.append(gbaSelect);
            for (let key in hoods){ // la key trae la clave principal (caba, zona_sur)
                gbaSelect.append($('<option>', {
                id: key,
                value: key,
                text: hoods[key].label
                }));
            };

            // ESCUCHAR LOS EVENTOS DE SELECCION EN SELECT DE ZONAS GBA
            gbaSelect.on('change', function(){
                // INSTANCIA DE ELIMINACION DE ELEMENTOS
                $('#custom_shipping_cost').remove()

                // CREACION DEL SELECT QUE CONTIENE LOS BARRIOS
                let hoodSelect = $('<select>', {
                    id: 'custom_shipping_cost',
                    class: 'form-row-radio'
                });

                customCheckoutField.append(hoodSelect)

                // AGREGA LAS OPCIONES AL SELECT DE BARRIOS CON LA DATA DEL OBJETO HOODS
                    for (let key in hoods){
                        let cityData = hoods[key].hoods;
                        if (gbaSelect.val() == key){
                            for (let hoodId in cityData){
                                let hood = cityData[hoodId];
                                let {name, slug, id, calculateDisabledDate, rangeDays, days} = hood;
                                hoodSelect.append($('<option>', {
                                id: slug,
                                value: slug,
                                text: name
                                }));

                                //  DATEPICKER
                                hoodSelect.on('change', function() {
                                    let datePickerElement = $('#custom_shipping_date_field')
                                    const today = new Date();
                                    const nextDay = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);

                                    datePickerElement.addClass('show');
                                    if (hoodSelect.val() == slug){
                                        console.log(slug)
                                        let customDatepicker = jQuery( '#custom_shipping_date' ).datepicker({
                                            dateFormat: 'dd-mm-yy',
                                            minDate: calculateDisabledDate,
                                            maxDate: rangeDays,

                                            beforeShowDay: function(date) {
                                                if (date.getTime() == nextDay.getTime()) {
                                                  // si es el día siguiente, inhabilitarlo
                                                  return [false];
                                                };
                                                let day = date.getDay()

                                                // Comprueba si el día actual está en la lista de días permitidos y es posterior al día siguiente
                                                if (date >= nextDay && days.includes(day)) {
                                                    return [true];
                                                } else {
                                                    return [false];
                                                }
                                            }
                                        });
                                    };
                                });

                            };
                        };
                    };

                // DATEPICKER
                // hoodSelect.on('change', function(){
                //     datePickerElement.addClass('show');
                //
                //     for (let key in hoods){
                //         let cityData = hoods[key].hoods;
                //         if (gbaSelect.val() == key){
                //             for (let hoodId in cityData){
                //                 let hood = cityData[hoodId];
                //                 let {name, slug, id, calculateDisabledDate} = hood;
                //                 if ()
                //                 hoodSelect.append($('<option>', {
                //                 id: slug,
                //                 value: slug,
                //                 text: name
                //                 }));
                //             };
                //         };
                //     };
                // });
            });
        };
    });
});

















// ACTUALIZACION DE COSTO DE ENVIO
jQuery(document).ready(function($) {
    $('body').on('change', 'select[id="custom_shipping_cost"]', function() {
        let zoneSelected = $('#gba_zones');
        let customShippingCost = $(this).val();
        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                action: 'actualizar_costo_envio',
                custom_shipping_cost: customShippingCost,
                zone_selected: zoneSelected.val()
            },
            success: function(response) {
                $('body').trigger('update_checkout');
            }
        });
    });
});

// DATEPICKER
// jQuery(document).ready(function($) {
//
//     // Declara una variable para mantener una referencia al datepicker
//     let customDatepicker = null;
//
//     $('#custom_shipping_cost').on('change', function(){
//         let zoneSelected = $('#gba_zones')
//         let selectSeleccionado = $(this).val();
//         let datePickerElement = $('#custom_shipping_date_field')
//         const today = new Date();
//         const nextDay = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1);
//
//         if (selectSeleccionado && zoneSelected.val() == 'caba') {
//             console.log('seee')
//             for (let key in capitalHoods){
//                 if (capitalHoods[key].slug === selectSeleccionado) {
//                     datePickerElement.addClass('show');
//
//                     // Destruye el datepicker existente si hay uno
//                     if (customDatepicker) {
//                         customDatepicker.datepicker('destroy');
//                     }
//
//                     customDatepicker = jQuery( '#custom_shipping_date' ).datepicker({
//                       dateFormat: 'dd-mm-yy',
//                       minDate: capitalHoods[key].calculateDisabledDate,
//                       maxDate: capitalHoods[key].rangeDays,
//
//                       beforeShowDay: function(date) {
//                         if (date.getTime() == nextDay.getTime()) {
//                           // si es el día siguiente, inhabilitarlo
//                           return [false];
//                         };
//                         let day = date.getDay()
//                         // Obtén la lista de días permitidos del objeto capitalHoods[key]
//                         let days = capitalHoods[key].days;
//
//                         // Comprueba si el día actual está en la lista de días permitidos y es posterior al día siguiente
//                         if (date >= nextDay && days.includes(day)) {
//                             return [true];
//                         } else {
//                             return [false];
//                         }
//                       }
//                   });
//                 }
//             }
//         } else if (selectSeleccionado && zoneSelected.val() == 'south_zone') {
//             for (let key in surHoods){
//                 if (surHoods[key].slug === selectSeleccionado) {
//                     datePickerElement.addClass('show');
//
//                     // Destruye el datepicker existente si hay uno
//                     if (customDatepicker) {
//                         customDatepicker.datepicker('destroy');
//                     }
//
//                     customDatepicker = jQuery( '#custom_shipping_date' ).datepicker({
//                       dateFormat: 'dd-mm-yy',
//                       minDate: surHoods[key].calculateDisabledDate,
//                       maxDate: surHoods[key].rangeDays,
//
//                       beforeShowDay: function(date) {
//                         if (date.getTime() == nextDay.getTime()) {
//                           // si es el día siguiente, inhabilitarlo
//                           return [false];
//                         };
//                         let day = date.getDay()
//                         // Obtén la lista de días permitidos del objeto capitalHoods[key]
//                         let days = capitalHoods[key].days;
//
//                         // Comprueba si el día actual está en la lista de días permitidos y es posterior al día siguiente
//                         if (date >= nextDay && days.includes(day)) {
//                             return [true];
//                         } else {
//                             return [false];
//                         }
//                       }
//                   });
//                 }
//             }
//         }
//     });
// });
//
//
// // CAPOS SELECT CON CONDICION
// jQuery(function($) {
//     // Escucha el evento de cambio de los radio buttons
//     $(document.body).on('change', 'input[name="radio_region"]', function(){
//         var radioSeleccionado = $(this).val();
//         var caba_hoods = $('.caba_hoods');
//         let south_hoods = $('.south_hoods');
//         var select = $('#custom_shipping_cost');
//         let datePickerElement = $('#custom_shipping_date_field')
//
//         // Obtener la opción con el valor "select_opt"
//         var optionToDisable = select.find('option[value="select_opt"]');
//         // Deshabilitar la opción
//         optionToDisable.prop('disabled', true);
//
//         if (radioSeleccionado == 'gba') {
//             datePickerElement.removeClass('show');
//             caba_hoods.removeClass('show');
//             $('#country_zones').remove();
//             var gbaSelect = $('<select>', {id: 'gba_zones'});
//             gbaSelect.append($('<option>', {
//                 id: 'select_opt',
//                 text: 'Selecicone una opcion',
//                 disabled: true,
//                 selected: true
//             }));
//             gbaSelect.append($('<option>', {
//                 id: 'caba',
//                 value: 'caba',
//                 text: 'Ciudad Autónoma de Buenos Aires'
//             }));
//             gbaSelect.append($('<option>', {
//                 id: 'north_zone',
//                 value: 'north_zone',
//                 text: 'Zona Norte'
//             }));
//             gbaSelect.append($('<option>', {
//                 id: 'west_zone',
//                 value: 'west_zone',
//                 text: 'Zona Oeste'
//             }));
//             gbaSelect.append($('<option>', {
//                 id: 'south_zone',
//                 value: 'south_zone',
//                 text: 'Zona Sur'
//             }));
//
//
//             // Agrega el nuevo campo select al contenedor
//             $('#radio_region_field').append(gbaSelect);
//
//             // Escucha el evento de cambio del campo select
//             gbaSelect.on('change', function() {
//                 var opcionSeleccionada = $(this).val();
//                 datePickerElement.removeClass('show');
//                 if (opcionSeleccionada === 'caba') {
//                     // Realiza acciones específicas para la opción 'caba'
//                     console.log('seleccionaste caba')
//                     // Crea el elemento select
//                     let customShippingField = $('<select>', {
//                       'class': ['form-row-radio', 'caba_hoods', 'hidden'],
//                       'name': 'custom_shipping_cost', // Nombre del campo
//                       'id': 'custom_shipping_cost',
//                       'required': true
//                     });
//                     // Agrega una opción predeterminada
//                     customShippingField.append($('<option>', {
//                       'value': 'select_opt', // Valor de la opción
//                       'text': 'Seleccione una opción' // Texto de la opción
//                     }));
//
//                     customShippingField.append($('<option>', {
//                     id: 'hood_palermo',
//                     value: 'hood_palermo',
//                     text: 'Palermo'
//                 }));
//
//                     $('#radio_region_field').append(customShippingField);
//                     caba_hoods.addClass('show');
//                 } else {
//                     caba_hoods.removeClass('show');
//                 }
//
//                 if (opcionSeleccionada === 'south_zone') {
//                     south_hoods.addClass('show');
//                     // Realiza acciones específicas para la opción 'sur_zone'
//                     console.log('Seleccionaste Zona Sur');
//                 }
//             });
//
//
//             console.log('gba seleccionado')
//         }
//         else if (radioSeleccionado == 'country') {
//             // ESTOY ACA
//             $('#custom_shipping_cost').remove();
//             datePickerElement.removeClass('show');
//             caba_hoods.removeClass('show');
//             $('#gba_zones').remove();
//             var countrySelect = $('<select>', {id: 'country_zones'});
//             countrySelect.append($('<option>', {
//                 id: 'select_opt',
//                 text: 'Selecicone una opcion',
//                 disabled: true,
//                 selected: true
//             }));
//             countrySelect.append($('<option>', {
//                 id: 'country_south_zone',
//                 value: 'country_south_zone',
//                 text: 'Zona Sur'
//             }));
//             countrySelect.append($('<option>', {
//                 id: 'country_north_zone',
//                 value: 'country_north_zone',
//                 text: 'Zona Norte'
//             }));
//             // Agrega el nuevo campo select al contenedor
//             $('#radio_region_field').append(countrySelect);
//             console.log('country seleccionado')
//         }
//     });
// });
