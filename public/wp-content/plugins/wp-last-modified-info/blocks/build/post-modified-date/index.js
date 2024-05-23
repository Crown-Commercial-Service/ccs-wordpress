(()=>{"use strict";var e={n:t=>{var o=t&&t.__esModule?()=>t.default:()=>t;return e.d(o,{a:o}),o},d:(t,o)=>{for(var i in o)e.o(o,i)&&!e.o(t,i)&&Object.defineProperty(t,i,{enumerable:!0,get:o[i]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.blocks,o=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"wplmi/post-modified-date","title":"Modified Date","description":"Display post\\"s last modified date.","icon":"calendar-alt","keywords":["last-modified","time","date","last-updated","updated"],"category":"theme","textdomain":"wp-last-modified-info","attributes":{"format":{"type":"string","default":""},"display":{"type":"string","default":"block"},"textAlign":{"type":"string","default":"left"},"textBefore":{"type":"string"},"textAfter":{"type":"string"},"varFontSize":{"type":"integer","default":16,"minimum":1},"varLineHeight":{"type":"string"},"varColorBackground":{"type":"string"},"varColorBorder":{"type":"string"},"varColorText":{"type":"string"}},"usesContext":["postId","postType","queryId"],"supports":{"html":false},"editorScript":"file:index.js"}'),i=window.React,l=window.wp.i18n,a=window.wp.serverSideRender;var n=e.n(a);const r=window.wp.components,d=window.wp.blockEditor;(0,t.registerBlockType)(o,{edit:({attributes:e,setAttributes:t})=>{const o=[{name:(0,l.__)("Tiny","wp-last-modified-info"),slug:"small",size:10},{name:(0,l.__)("Small","wp-last-modified-info"),slug:"small",size:14},{name:(0,l.__)("Normal","wp-last-modified-info"),slug:"normal",size:16},{name:(0,l.__)("Big","wp-last-modified-info"),slug:"big",size:20}];return(0,i.createElement)("div",{...(0,d.useBlockProps)()},(0,i.createElement)(n(),{block:"wplmi/post-modified-date",attributes:e}),(0,i.createElement)(d.InspectorControls,{key:"settings"},(0,i.createElement)(r.PanelBody,{title:(0,l.__)("Options","wp-last-modified-info"),initialOpen:!1},(0,i.createElement)(r.TextControl,{label:(0,l.__)("Format","wp-last-modified-info"),help:(0,l.__)("Date Time format. Leave blank for default.","wp-last-modified-info"),value:e.format,onChange:e=>t({format:e})}),(0,i.createElement)(r.SelectControl,{label:(0,l.__)("Display","wp-last-modified-info"),value:e.display,options:[{label:"Block",value:"block"},{label:"Inline",value:"inline"}],onChange:e=>t({display:e})}),(0,i.createElement)(r.SelectControl,{label:(0,l.__)("Text Align","wp-last-modified-info"),value:e.textAlign,options:[{label:"Left",value:"left"},{label:"Center",value:"center"},{label:"Right",value:"right"}],onChange:e=>t({textAlign:e})})),(0,i.createElement)(r.PanelBody,{title:(0,l.__)("Content","wp-last-modified-info"),initialOpen:!1},(0,i.createElement)(r.TextControl,{label:(0,l.__)("Text Before","wp-last-modified-info"),help:(0,l.__)("Text to show before the timestamp","wp-last-modified-info"),value:e.textBefore,onChange:e=>t({textBefore:e})}),(0,i.createElement)(r.TextControl,{label:(0,l.__)("Text After","wp-last-modified-info"),help:(0,l.__)("Text to show after the timestamp","wp-last-modified-info"),value:e.textAfter,onChange:e=>t({textAfter:e})})),(0,i.createElement)(r.PanelBody,{title:(0,l.__)("Typography","wp-last-modified-info")},(0,i.createElement)(r.FontSizePicker,{label:(0,l.__)("Font Size","wp-last-modified-info"),value:e.varFontSize,onChange:e=>t({varFontSize:e}),fallBackFontSize:16,fontSizes:o}),(0,i.createElement)(d.LineHeightControl,{label:(0,l.__)("Line Height","wp-last-modified-info"),value:e.varLineHeight,onChange:e=>t({varLineHeight:e})})),(0,i.createElement)(r.PanelBody,{title:(0,l.__)("Colors","wp-last-modified-info")},(0,i.createElement)(d.ColorPaletteControl,{label:(0,l.__)("Background","wp-last-modified-info"),value:e.varColorBackground,onChange:e=>t({varColorBackground:e})}),(0,i.createElement)(d.ColorPaletteControl,{label:(0,l.__)("Text","wp-last-modified-info"),value:e.varColorText,onChange:e=>t({varColorText:e})}))))}})})();