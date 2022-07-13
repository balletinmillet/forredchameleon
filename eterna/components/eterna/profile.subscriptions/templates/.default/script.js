(function() {
    'use strict';

    BX.EternaSubscribe = {

        initializePrimaryFields: function ()
        {
            this.mainContainer = [];
            this.result = {};
        },

        init: function(parameters)
        {
            this.initializePrimaryFields(parameters);
            this.mainContainer = BX(parameters.mainContainerId);
            this.result = parameters.result;
            this.bindEvents();
        },

        bindEvents: function()
        {
            var saveButton;

            saveButton = this.mainContainer.querySelector('a[name="save"]');
            if(saveButton)
                BX.bind(saveButton, 'click', BX.proxy(this.submitFormHandler, this));
        },

        getInputCheckByName: function(name)
        {
            var input = this.mainContainer.querySelector('input[name="' + name + '"]');
            if(input)
                return input.checked;

            return '';
        },

        saveInputsState: function()
        {
            var key;
            for (key in this.result)
                this.result[key].isSubscribed = this.getInputCheckByName(key);
        },

        saveButtonSaveState: function(state)
        {
            var saveButton;

            saveButton = this.mainContainer.querySelector('a[name="save"]');
            if(saveButton)
            {
                if(state == 'active')
                    BX.removeClass(saveButton, 'inactive');
                else if(state == 'inactive')
                    BX.addClass(saveButton, 'inactive');
            }

        },
        
        submitFormHandler: function(event)
        {
            this.saveInputsState();
            this.saveButtonSaveState('inactive');

            this.sendRequest(
                'saveSubscription',
                {
                    arFields: {
                        'subscriptions': this.result,
                    }
                },
                {
                    "app": this,
                }
            );
        },

        showResultMessage: function(resultStatus, resultMessage)
        {
            var resultMessageContainer, resultNode, oldResultNode;

            this.saveButtonSaveState('active');

            resultMessageContainer = this.mainContainer.querySelector('.result-message-container');
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

        saveSubscriptionHandler: function(response, contextParams)
        {
            console.log(response.data);
            if(response.data.hasOwnProperty('EDIT_RESULT'))
            {
                if(response.data.EDIT_RESULT.status == 'Success')
                    contextParams.app.showResultMessage('success', 'Изменения сохранены');
                else
                    contextParams.app.showResultMessage('error', 'Ошибка сохранения');
            }
        },

        setRequestResult: function(func, response, contextParams)
        {
            switch (func)
            {
                case 'saveSubscription':
                    this.saveSubscriptionHandler(response, contextParams);
                    break;
            }
        },

        sendRequest: function(func, fucnParams, contextParams)
        {
            BX.ajax.runComponentAction('custom:profile.subscriptions', func, {
                mode: 'class',
                data: fucnParams
            }).then(function(response) {
                contextParams.app.setRequestResult(func, response, contextParams);
            }, this).catch((response) => {
                console.log(response);
                console.log(fucnParams);
                console.log(contextParams);
                console.log('Ошибка выполнения запроса!');
            });
        },


    };
})();