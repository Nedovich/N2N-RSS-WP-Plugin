(function (blocks, element, blockEditor, components, serverSideRender) {
  var el = element.createElement;
  var registerBlockType = blocks.registerBlockType;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var SelectControl = components.SelectControl;
  var RangeControl = components.RangeControl;
  var TextControl = components.TextControl;
  var ToggleControl = components.ToggleControl;
  var ServerSideRender = serverSideRender;

  registerBlockType("n2n-aggregator/news-feed", {
    title: "Aggregated News Feed",
    icon: "rss",
    category: "widgets",
    attributes: {
      layout: { type: "string", default: "grid" },
      postsToShow: { type: "number", default: 6 },
      categoryId: { type: "string", default: "" },
      tagId: { type: "string", default: "" },
      showImage: { type: "boolean", default: true },
      showExcerpt: { type: "boolean", default: true },
      openInNewTab: { type: "boolean", default: true },
    },
    edit: function (props) {
      var attributes = props.attributes;
      var setAttributes = props.setAttributes;

      return [
        el(
          InspectorControls,
          { key: "inspector" },
          el(
            PanelBody,
            { title: "Settings", initialOpen: true },
            el(SelectControl, {
              label: "Layout",
              value: attributes.layout,
              options: [
                { label: "Grid", value: "grid" },
                { label: "List", value: "list" },
              ],
              onChange: function (val) {
                setAttributes({ layout: val });
              },
            }),
            el(RangeControl, {
              label: "Posts Limit",
              value: attributes.postsToShow,
              min: 1,
              max: 40,
              onChange: function (val) {
                setAttributes({ postsToShow: val });
              },
            }),
            el(TextControl, {
              label: "Category ID",
              value: attributes.categoryId,
              onChange: function (val) {
                setAttributes({ categoryId: val });
              },
            }),
            el(TextControl, {
              label: "Tag ID",
              value: attributes.tagId,
              onChange: function (val) {
                setAttributes({ tagId: val });
              },
            }),
            el(ToggleControl, {
              label: "Show Image",
              checked: attributes.showImage,
              onChange: function (val) {
                setAttributes({ showImage: val });
              },
            }),
            el(ToggleControl, {
              label: "Show Excerpt",
              checked: attributes.showExcerpt,
              onChange: function (val) {
                setAttributes({ showExcerpt: val });
              },
            }),
            el(ToggleControl, {
              label: "Open in New Tab",
              checked: attributes.openInNewTab,
              onChange: function (val) {
                setAttributes({ openInNewTab: val });
              },
            })
          )
        ),
        el(ServerSideRender, {
          key: "render",
          block: "n2n-aggregator/news-feed",
          attributes: attributes,
        }),
      ];
    },
    save: function () {
      return null;
    },
  });
})(
  window.wp.blocks,
  window.wp.element,
  window.wp.blockEditor,
  window.wp.components,
  window.wp.serverSideRender
);
