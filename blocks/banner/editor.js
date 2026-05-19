(function (wp) {
    if (!wp || !wp.blocks || !wp.element || !wp.blockEditor) return;

    var el = wp.element.createElement;
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var PanelBody = wp.components.PanelBody;
    var SelectControl = wp.components.SelectControl;
    var ServerSideRender = wp.serverSideRender || (wp.editor && wp.editor.ServerSideRender);

    var bannerOptions = [];
    for (var i = 1; i <= 5; i++) {
        bannerOptions.push({ label: 'Banner #' + i, value: i });
    }

    registerBlockType('site-banner/banner', {
        edit: function (props) {
            var blockProps = useBlockProps();
            return el(
                'div',
                blockProps,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: 'Banner', initialOpen: true },
                        el(SelectControl, {
                            label: 'Which banner to render',
                            value: props.attributes.bannerId,
                            options: bannerOptions,
                            onChange: function (v) { props.setAttributes({ bannerId: parseInt(v, 10) || 1 }); },
                        })
                    )
                ),
                ServerSideRender
                    ? el(ServerSideRender, { block: 'site-banner/banner', attributes: props.attributes })
                    : el('div', { style: { padding: '10px', background: '#f0f0f1' } },
                        'Site Banner #' + props.attributes.bannerId + ' will render here on the front-end.')
            );
        },
        save: function () { return null; },
    });
})(window.wp);
