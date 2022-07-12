function bundleWarranties(config) {
    var widget =
        {
            bundleConfig: [],
            componentIdPrefix: '#extend-bundle-offer-',
            hasWarranties: false,
            init: function (config) {
                var self = this;
                if (window.bundle == undefined) {
                    return;
                }
                this.bundleConfig = config;
                if (window.bundle.config) {
                    jQuery.each(window.bundle.config.options, function (i, bundleOption) {
                        jQuery('#bundle-option-' + i + '-tier-prices')
                            .parent()
                            .append(
                                "<div id='extend-bundle-offer-" + i + "' data-option='" + i + "'></div>"
                            );
                    });
                }
                ;


                $(document).observe('bundle:reload-price', function (event) {
                    var data = event.memo, bundle = data.bundle;

                    jQuery.each(bundle.config.selected, function (optionId, selectionId) {
                        console.log(optionId + ', ' + selectionId + ', ' + self.bundleConfig[selectionId]);
                        this.renderWarranties(optionId, self.bundleConfig[selectionId]);
                    }.bind(this));
                }.bind(this));
                return this;
            },
            addToCartCallback: function () {
                let self = this;
                self.hasWarranties = false;
                jQuery.each(bundle.config.selected, function (optionId, selectionId) {
                    optionComponent = self._getExtendComponent(optionId);
                    if (optionComponent.getPlanSelection()) {
                        self.hasWarranties = true;
                        self.addBundleWarranties(
                            optionId,
                            optionComponent.getPlanSelection(),
                            optionComponent.getActiveProduct().id
                        );

                    }
                });
            },
            addBundleWarranties: function (optionId, plan, sku) {
                $j.each(plan, (attribute, value) => {
                    $j('<input />').attr('type', 'hidden')
                        .attr('name', 'warranties[' + optionId + '][' + attribute + ']')
                        .attr('value', value)
                        .appendTo('#product_addtocart_form');
                });

                $j('<input />').attr('type', 'hidden')
                    .attr('name', 'warranties[' + optionId + '][product]')
                    .attr('value', sku)
                    .appendTo('#product_addtocart_form');
            },
            _getExtendComponent: function (optionId) {
                let componentId = this._getComponentId(optionId);
                return Extend.buttons.instance(componentId);
            },
            _getComponentId: function (optionId) {
                return this.componentIdPrefix + optionId;
            },

            renderWarranties: function (optionId, productSku) {
                const component = this._getExtendComponent(optionId);
                if (component !== null ) {
                    if (!component.getActiveProduct() || component.getActiveProduct().id !== productSku) {
                        component.setActiveProduct(productSku);
                    }
                } else {
                    Extend.buttons.render(
                        this._getComponentId(optionId), {
                            referenceId: productSku
                        }
                    );
                }
            }
        };

    return widget.init(config);
};
