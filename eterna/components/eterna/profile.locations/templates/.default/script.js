(function() {
    'use strict';

    BX.EternaLocationHandler = {

        mainContainerNode: [],
        cityInputNode: [],
        streetInputNode: [],
        buildingInputNode: [],
        ajaxPath: '',
        locationCodes: [],
        result: {},

        init: function (params) {
            this.initFields(params);
            this.bindEvents();
        },

        bindEvents: function (params) {

            var saveButton, addButton;

            BX.bind(this.cityInputNode, 'keyup', BX.proxy(this.getLocation, this));
            BX.bind(this.streetInputNode, 'keyup', BX.proxy(this.getLocation, this));
            BX.bind(this.buildingInputNode, 'keyup', BX.proxy(this.getLocation, this));

            saveButton = this.mainContainer.querySelector('a[name="save"]');
            if(saveButton)
                BX.bind(saveButton, 'click', BX.proxy(this.submitFormHandler, this));

            addButton = this.mainContainer.querySelector('a[name="add"]');
            if(addButton)
                BX.bind(addButton, 'click', BX.proxy(this.showLocationForm, this));
        },

        initFields: function (params) {
            this.params = params;
            this.mainContainer = BX(params.mainContainerId);
            this.cityInputNode = this.mainContainer.querySelector('input[name="CITY"]');
            this.streetInputNode = this.mainContainer.querySelector('input[name="STREET"]');
            this.buildingInputNode = this.mainContainer.querySelector('input[name="BUILDING"]');
            this.locationFormNode = BX(params.locationFormId);
            this.ajaxPath = params.ajaxPath;
            this.lamodaDeliveries = {};
        },

        getInputValues: function()
        {
            var inputs, values = {}, name, locationId, value;
            var inputs = this.locationFormNode.querySelectorAll('input.ui-autocomplete-input');
            if(inputs)
            {
                for (let i = 0; i < inputs.length; i++)
                {
                    name = inputs[i].getAttribute('name');
                    if(name == null)
                        continue;

                    locationId = inputs[i].getAttribute('data-location-id') == null ? "" : inputs[i].getAttribute('data-location-id');
                    value = inputs[i].value;
                    values[name] = {'value': value, 'code': locationId};
                }
            }

            return values;
        },

        showLocationForm: function () {

            var app = this;
            this.result.locationPopup = BX.PopupWindowManager.create("locationPopup", null, {
                autoHide: true,
                offsetLeft: 0,
                offsetTop: 0,
                overlay: true,
                draggable: {restrict: true},
                closeByEsc: true,
                closeIcon: {right: "12px", top: "10px"},
                content: BX('location-popup'),
                events: {
                    onAfterPopupShow: function () {},
                    onPopupClose: function () {}
                }
            });

            this.result.locationPopup.show();
        },

        showResultMessage: function(resultStatus, resultMessage)
        {
            var resultMessageContainer, resultNode, oldResultNode;

            this.saveButtonSaveState('active');

            resultMessageContainer = this.locationFormNode.querySelector('.result-message-container');
            if(resultMessageContainer)
            {
                oldResultNode = resultMessageContainer.querySelector('.result-message');
                if(oldResultNode)
                    BX.remove(oldResultNode);

                resultNode = BX.create('DIV', {
                    props: {className: 'result-message ' + resultStatus},
                    text: resultMessage,
                });

                resultMessageContainer.appendChild(resultNode);
            }
        },

        saveButtonSaveState: function(state)
        {
            var saveButton;

            saveButton = this.locationFormNode.querySelector('a[name="save"]');
            if(saveButton)
            {
                if(state == 'active')
                    BX.removeClass(saveButton, 'inactive');
                else if(state == 'inactive')
                    BX.addClass(saveButton, 'inactive');
            }
        },

        isEmptyObject: function(obj)
        {
            for (var i in obj) {
                if (obj.hasOwnProperty(i)) {
                    return false;
                }
            }

            return true;
        },

        submitFormHandler: function(event)
        {
            if(this.isEmptyObject(this.lamodaDeliveries))
            {
                this.showResultMessage('error', 'По вашему адресу не найдены способы доставки');
                return;
            }
            else
            {
                this.showResultMessage('', '');
            }
            
            this.sendComponentRequest(
                'saveLocation',
                {
                    arFields: {
                        'locations': this.getInputValues(),
                    }
                },
                {
                    "app": this,
                }
            );
        },

        getLocationsListNode: function(list, locationType)
        {
            var options = [];
            for (let i = 0; i < list.length; i++)
            {
                options.push(
                    BX.create('LI', {
                        props: {
                            className: 'ui-menu-item',
                        },
                        children: [
                            BX.create('DIV', {
                                props: {
                                    id: 'id="ui-id-' + i,
                                    className: 'ui-menu-item-wrapper',
                                },
                                attrs: {
                                    'data-id' : list[i].id,
                                    'data-zipcode' : list[i].zipcode,
                                    'data-name' : locationType == "CITY" ? list[i].text_extended : list[i].text,
                                    'data-input-name' : locationType,
                                },
                                text: locationType == "CITY" ? list[i].text_extended : list[i].text,
                            })
                        ],
                        events: {click: BX.proxy(this.selectLocation, this)}
                    })
                );
            }

            return BX.create('UL', {
                props: {
                    id: 'ui-location-list',
                    className: 'ui-menu ui-widget ui-widget-content ui-autocomplete ui-front',
                },
                children: options
            });
        },

        removeDeliveryBlock: function()
        {
            var deliveryContainer, itemsContainer;
            deliveryContainer = BX('soa-lamoda-deliveries');
            if(deliveryContainer)
            {
                itemsContainer = deliveryContainer.querySelector('.select-items-container');
                if (itemsContainer)
                    BX.remove(itemsContainer);

                itemsContainer = deliveryContainer.querySelector('.lamoda-delivery-description');
                if (itemsContainer)
                    BX.remove(itemsContainer);
            }
        },

        showLocations: function(response, contextParams)
        {
            var listNode, fieldNode;
            if(!response.data)
            {
                this.removeDeliveryBlock();
                BX.remove(BX('ui-location-list'));
                return;
            }
            else if(response.data.length == 0)
            {
                this.removeDeliveryBlock();
                BX.remove(BX('ui-location-list'));
                return;
            }

            listNode = this.getLocationsListNode(response.data, contextParams.locationType);
            if(listNode)
            {
                fieldNode = BX.findParent(contextParams.clickedNode, {className: 'soa-property-container'});
                if(fieldNode)
                {
                    BX.remove(BX('ui-location-list'));
                    fieldNode.appendChild(listNode);
                }
            }
        },

        selectLocation: function(event)
        {
            var clickedNode = event.target || event.srcElement,
                locationId, locationName, locationType, input, parentNode, requestParams, contextParams;

            locationId = clickedNode.getAttribute('data-id');
            locationName = clickedNode.getAttribute('data-name');
            locationType = clickedNode.getAttribute('data-input-name');

            if(locationType == null || locationId == null)
                return;

            parentNode = BX.findParent(clickedNode, {className: 'soa-property-container'});
            if(parentNode)
            {
                input = parentNode.querySelector('input');
                if(input)
                {
                    this.locationCodes[locationType] = locationId;
                    input.setAttribute('data-location-id', locationId);
                    input.setAttribute('data-location-type', locationType);
                    input.value = locationName;
                    BX.remove(BX('ui-location-list'));

                }
            }

            if(locationType == "BUILDING")
            {
                if(!this.locationCodes["BUILDING"])
                    return;

                requestParams = {
                    'locationType': 'DELIVERY',
                    'parentLocationId': this.locationCodes["BUILDING"],
                    'inputText': '',
                };

                contextParams = {
                    "app": this,
                    "clickedNode": clickedNode,
                    'locationType': locationType,
                    'functype': 'delivery',
                };

                this.sendRequest(requestParams, contextParams);

            }
        },

        clearDeliveriesByCity: function()
        {
            var inputs;
            var inputs = this.locationFormNode.querySelectorAll('input.ui-autocomplete-input');
            if(inputs)
            {
                for (let i = 0; i < inputs.length; i++)
                {
                    if(inputs[i].getAttribute('name') != "CITY")
                    {
                        inputs[i].value = "";
                        inputs[i].setAttribute('data-location-id', '');
                    }
                }
            }

            this.lamodaDeliveries = {};
        },

        clearDeliveriesByStreet: function()
        {
            var inputs;
            var inputs = this.locationFormNode.querySelectorAll('input.ui-autocomplete-input');
            if(inputs)
            {
                for (let i = 0; i < inputs.length; i++)
                {
                    if(inputs[i].getAttribute('name') != "CITY" && inputs[i].getAttribute('name') != "STREET")
                    {
                        inputs[i].value = "";
                        inputs[i].setAttribute('data-location-id', '');
                    }
                }
            }

            this.lamodaDeliveries = {};
        },

        getLocation: function(event)
        {
            var clickedNode = event.target || event.srcElement, property, contextParams, params,
                locationType, parentLocationId, parentInput, requestParams;

            locationType = clickedNode.name;
            parentLocationId = '';

            if(locationType == "CITY")
                this.clearDeliveriesByCity();

            if(locationType == "STREET")
                this.clearDeliveriesByStreet();

            if(locationType == "CITY" && clickedNode.value.length < 2)
                parentLocationId = this.locationCodes["CITY"];
            if(locationType == "STREET")
                parentLocationId = this.locationCodes["CITY"];
            if(locationType == "BUILDING")
                parentLocationId = this.locationCodes["STREET"];

            requestParams = {
                'locationType': locationType,
                'parentLocationId': parentLocationId,
                'inputText': clickedNode.value,
            };

            contextParams = {
                "app": this,
                "clickedNode": clickedNode,
                'locationType': locationType,
                'functype': 'location',
            };

            this.sendRequest(requestParams, contextParams);
        },

        getLocalDate: function(dateString)
        {
            var parts = dateString.split('-');
            return parts[2] + '.' + parts[1] + '.' + parts[0];
            //var date = new Date(parts[0], parts[1] - 1, parts[2]);
            //return date.toDateString('ru');
        },

        createLamodaDeliveryDescriptionBlock: function(delivery)
        {
            var itemNode = [];

            itemNode = BX.create('DIV', {
                props: {className: 'lamoda-delivery-description'},
                children: [
                    BX.create('DIV', {
                        props: {className: 'desc-item fitting'},
                        html: '<b>Тип доставки: </b>' + delivery.serviceLevelTypeName
                    }),
                    BX.create('DIV', {
                        props: {className: 'desc-item cost'},
                        html: '<b>Стоимость доставки: </b>' + delivery.checkoutMethodDeliveryPrice + ' рублей'
                    }),
                    BX.create('DIV', {
                        props: {className: 'desc-item free-sum'},
                        html: '<b>Бесплатная доставка при выкупе: </b>' + delivery.checkoutMethodFreeDeliveryNetThreshold + ' рублей'
                    }),
                    /*                        BX.create('DIV', {
                                                props: {className: 'desc-item intervalId'},
                                                html: '<b>Код места доставки: </b>' + delivery.intervalId
                                            }),*/
                    BX.create('DIV', {
                        props: {className: 'desc-item desc'},
                        html: delivery.checkoutMethodCheckoutDescription
                    }),
                ]
            });

            return itemNode;
        },

        selectLamodaDelivery: function(event)
        {
            var clickedNode = event.target || event.srcElement;
            this.updateDeliveryDescription(this.lamodaDeliveries[clickedNode.value]);
        },

        updateDeliveryDescription: function(delivery)
        {
            var deliveryContainer, itemsContainer, deliveryDescription;

            deliveryContainer = BX('soa-lamoda-deliveries');
            if(deliveryContainer)
            {
                deliveryDescription = this.createLamodaDeliveryDescriptionBlock(delivery);
                itemsContainer = deliveryContainer.querySelector('.lamoda-delivery-description');
                if (itemsContainer)
                    BX.remove(itemsContainer);

                deliveryContainer.appendChild(deliveryDescription);
            }
        },

        showLamodaDeliveries: function(response, contextParams)
        {
            var deliveryContainer, itemsContainer;

            deliveryContainer = BX('soa-lamoda-deliveries');
            if(deliveryContainer)
            {
                itemsContainer = deliveryContainer.querySelector('.select-items-container');
                if (itemsContainer)
                    BX.remove(itemsContainer);

                itemsContainer = deliveryContainer.querySelector('.lamoda-delivery-description');
                if (itemsContainer)
                    BX.remove(itemsContainer);
            }


            this.createLamodaDeliveryBlock(response, 0);
            this.lamodaDeliveries = response;
        },

        createLamodaDeliveryItem: function(item, itemId, checked)
        {
            var deliveryId = parseInt(itemId);
            return BX.create('OPTION', {
                props: {
                    className: 'location-item',
                    value: deliveryId
                },
                attrs: checked ? {selected: true} : {},
                text: this.getLocalDate(item.dayDate) + ' ' + item.intervalStart + ' - ' + item.intervalEnd,

            });
        },

        createLamodaDeliveryBlock: function(deliveryList, checkedItemId)
        {
            if(!deliveryList)
                return;

            var items = [], itemsContainer, deliveryContainer, lastSelectedItem, deliveryDescriptionBlock;

            checkedItemId = checkedItemId > -1 ? checkedItemId : 0;

            deliveryContainer = BX('soa-lamoda-deliveries');
            if(deliveryContainer)
            {
                itemsContainer = deliveryContainer.querySelector('.select-items-container');
                if(itemsContainer)
                    BX.remove(itemsContainer);

                itemsContainer = deliveryContainer.querySelector('.lamoda-delivery-description');
                if(itemsContainer)
                    BX.remove(itemsContainer);

                for (let i = 0; i < deliveryList.length; i++)
                    items.push(this.createLamodaDeliveryItem(deliveryList[i], i, i == checkedItemId))

                itemsContainer = BX.create('SELECT', {
                    props: {className: 'select-items-container'},
                    children: items,
                    events: {change: BX.proxy(this.selectLamodaDelivery, this)}
                });


                deliveryDescriptionBlock = this.createLamodaDeliveryDescriptionBlock(deliveryList[checkedItemId]);

                deliveryContainer.appendChild(itemsContainer);
                deliveryContainer.appendChild(deliveryDescriptionBlock);
            }
        },

        saveLocationHandler: function(response, contextParams)
        {
            if(response.data.hasOwnProperty('STATUS'))
            {
                if(response.data.STATUS)
                    location.reload();
            }
        },

        setRequestResult: function(response, contextParams)
        {
            switch (contextParams.functype)
            {
                case 'location':
                    this.showLocations(response, contextParams);
                    break;
                case 'delivery':
                    this.showLamodaDeliveries(response, contextParams);
                    break;
            }
        },

        setComponentRequestResult: function(func, response, contextParams)
        {
            switch (func)
            {
                case 'saveLocation':
                    this.saveLocationHandler(response, contextParams);
                    break;
            }
        },

        sendRequestFailure: function()
        {
            //NProgress.done();
        },

        sendRequest: function(basketParams, contextParams)
        {
            //NProgress.start();
            BX.ajax({
                method: 'POST',
                dataType: 'json',
                url: this.ajaxPath,
                data: basketParams,
                onsuccess: BX.delegate(function(response) {
                    contextParams.app.setRequestResult(response, contextParams);
                    //NProgress.done();
                }, this),
                onfailure: BX.delegate(function(error) {

                    //NProgress.done();

                }, this),

            });
        },

        sendComponentRequest: function(func, fucnParams, contextParams)
        {
            BX.ajax.runComponentAction('custom:profile.locations', func, {
                mode: 'class',
                data: fucnParams
            }).then(function(response) {
                contextParams.app.setComponentRequestResult(func, response, contextParams);
            }, this).catch((response) => {
                console.log(response);
                console.log(fucnParams);
                console.log(contextParams);
                console.log('Ошибка выполнения запроса!');
            });
        },

    }

})();