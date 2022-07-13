(function() {
    'use strict';

    BX.QuantumCouponMaenager = {

        initializePrimaryFields: function ()
        {
            this.mainContainer = [];
            this.couponCode = "";
            this.productId = 0;
            this.basePriceNodeId = [];
        },

        init: function(parameters)
        {
            this.initializePrimaryFields(parameters);
            this.mainContainer = BX(parameters.mainContainerId);
            this.basePriceNode = BX(parameters.basePriceNodeId);
            this.productId = parameters.productId;
            this.bindEvents();
        },

        bindEvents: function()
        {
            var input, buttonCouponApply, buttonCouponRemove;

            input = this.mainContainer.querySelector('.bx-soa-coupon-input input');
            if(input)
            {
                //BX.bind(input, 'change', BX.proxy(this.applyCoupon, this));
            }

            buttonCouponApply = this.mainContainer.querySelector('.coupon-apply');
            if(buttonCouponApply)
                BX.bind(buttonCouponApply, 'click', BX.proxy(this.applyCoupon, this));

            buttonCouponRemove = this.mainContainer.querySelector('.coupon-remove');
            if(buttonCouponRemove)
                BX.bind(buttonCouponRemove, 'click', BX.proxy(this.removeCoupon, this));

            //BX.bind(window, 'scroll', BX.proxy(this.loadContent, this));
        },

        getCouponCode: function()
        {
            var input = this.mainContainer.querySelector('.bx-soa-coupon-input input');
            if(input)
                return input.value;
        },

        applyCoupon: function()
        {
            var couponCode = this.getCouponCode();

            if(couponCode.length > 0 && this.productId > 0)
            {
                this.sendCustomRequest(
                    'getApplyResult',
                    {
                        arFields: {
                            'couponCode': couponCode,
                            'productId': this.productId,
                        }
                    },
                    {
                        "app": this,
                    }
                );
            }
        },

        removeCoupon: function()
        {
            this.sendCustomRequest(
                'removeCoupon',
                {
                    arFields: {}
                },
                {
                    "app": this,
                }
            );
        },

        createDiscountLine: function(discountPrice)
        {
            return BX.create('DIV', {
                props: {className: 'bx-soa-cart-total-line calc-row discount-row'},
                children: [
                    BX.create('SPAN', { props: { className: 'bx-soa-cart-t'}, text: 'Цена со скидкой ' }),
                    BX.create('SPAN', { props: { className: 'bx-soa-cart-d'}, text: discountPrice + ' руб.' }),
                ]
            });
        },

        removeDiscountLine: function()
        {
            var discountRow = this.mainContainer.querySelector('.discount-row');
            if(discountRow)
                BX.remove(discountRow);
        },

        setBasePriceAvailable: function(status = "available")
        {
            if(this.basePriceNode)
            {
                if(status == "available")
                    BX.removeClass(this.basePriceNode, 'bx-soa-coupon-old-price');
                else if(status == "unavailable")
                    BX.addClass(this.basePriceNode, 'bx-soa-coupon-old-price');
            }
        },

        showCouponResult: function(status, result = {})
        {
            var statusItemNode, statusItemsNode;

            this.removeDiscountLine();
            this.setBasePriceAvailable();

            statusItemNode = this.mainContainer.querySelector('.bx-soa-coupon-item');
            statusItemsNode = this.mainContainer.querySelector('.bx-soa-coupon-items');

            if(statusItemNode && statusItemsNode)
            {
                if(status == "success")
                {
                    BX.removeClass(statusItemNode, 'error');
                    BX.addClass(statusItemNode, 'success');
                    statusItemNode.innerText = 'Промокод применен';
                    this.setBasePriceAvailable("unavailable");
                    this.mainContainer.appendChild(this.createDiscountLine(result.PRICE));
                }
                else
                {
                    BX.removeClass(statusItemNode, 'success');
                    BX.addClass(statusItemNode, 'error');
                    statusItemNode.innerText = 'Промокод не найден';
                }

                statusItemsNode.style.display = 'inline-block';
            }
        },

        applyResultHandler: function(response, contextParams)
        {
            if(response.data)
            {
                if(response.data.hasOwnProperty('PRICE'))
                    contextParams.app.showCouponResult('success', response.data);
                else
                    contextParams.app.showCouponResult('error');
            }
            else
            {
                contextParams.app.showCouponResult('error');
            }
        },

        removeCouponHandler: function(response, contextParams)
        {
            var statusItemNode, statusItemsNode, couponInput;

            this.removeDiscountLine();
            this.setBasePriceAvailable();

            statusItemNode = this.mainContainer.querySelector('.bx-soa-coupon-item');
            statusItemsNode = this.mainContainer.querySelector('.bx-soa-coupon-items');

            if(statusItemNode && statusItemsNode)
            {
                statusItemNode.innerText = '';
                statusItemsNode.style.display = 'none';
            }

            couponInput = this.mainContainer.querySelector('.bx-soa-coupon-input input');
            if(couponInput)
                couponInput.value = '';
        },

        setRequestResult: function(func, response, contextParams)
        {
            switch (func)
            {
                case 'getApplyResult':
                    this.applyResultHandler(response, contextParams);
                    break;
                case 'removeCoupon':
                    this.removeCouponHandler(response, contextParams);
                    break;
            }
        },

        sendCustomRequest: function(func, fucnParams, contextParams)
        {
            BX.ajax.runComponentAction('quantum:coupon.manager', func, {
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