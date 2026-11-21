/*
 * wysiwyg web editor
 *
 * suneditor.js
 * Copyright 2019 JiHong Lee.
 * MIT license.
 */
'use strict';

(function (global, factory) {
    if (typeof module === 'object' && typeof module.exports === 'object') {
        module.exports = global.document ?
            factory(global, true) :
            function (w) {
                if (!w.document) {
                    throw new Error('SUNEDITOR_LANG a window with a document');
                }
                return factory(w);
            };
    } else {
        factory(global);
    }
}(typeof window !== 'undefined' ? window : this, function (window, noGlobal) {
    const lang = {
        code: 'zh_cn',
        toolbar: {
            default: '預設',
            save: '儲存',
            font: '字型',
            formats: '格式',
            fontSize: '字號',
            bold: '粗體',
            underline: '下劃線',
            italic: '斜體',
            strike: '刪除線',
            subscript: '下標',
            superscript: '上標',
            removeFormat: '清除格式',
            fontColor: '字型顏色',
            hiliteColor: '背景顏色',
            indent: '增加縮排',
            outdent: '減少縮排',
            align: '對齊方式',
            alignLeft: '左對齊',
            alignRight: '右對齊',
            alignCenter: '居中',
            alignJustify: '兩端對齊',
            list: '列表',
            orderList: '有序列表',
            unorderList: '無序列表',
            horizontalRule: '水平線',
            hr_solid: '實線',
            hr_dotted: '點線',
            hr_dashed: '虛線',
            table: '表格',
            link: '超連結',
            math: '數學',
            image: '圖片',
            video: '影片',
            audio: '音訊',
            fullScreen: '全屏',
            showBlocks: '顯示塊區域',
            codeView: '程式碼檢視',
            undo: '撤消',
            redo: '恢復',
            preview: '預覽',
            print: '列印',
            tag_p: '段落',
            tag_div: '正文 (DIV)',
            tag_h: '標題',
            tag_blockquote: '引用',
            tag_pre: '程式碼',
            template: '模板',
            lineHeight: '行高',
            paragraphStyle: '段落樣式',
            textStyle: '文字樣式',
            imageGallery: '圖片庫',
            dir_ltr: '左到右',
            dir_rtl: '右到左',
            mention: '提到'
        },
        dialogBox: {
            linkBox: {
                title: '插入超連結',
                url: '網址',
                text: '要顯示的文字',
                newWindowCheck: '在新標籤頁中開啟',
                downloadLinkCheck: '下載連結',
                bookmark: '書籤'
            },
            mathBox: {
                title: '數學',
                inputLabel: '數學符號',
                fontSizeLabel: '字號',
                previewLabel: '預覽'
            },
            imageBox: {
                title: '插入圖片',
                file: '上傳圖片',
                url: '圖片網址',
                altText: '替換文字'
            },
            videoBox: {
                title: '插入影片',
                file: '上傳圖片',
                url: '嵌入網址, Youtube,Vimeo'
            },
            audioBox: {
                title: '插入音訊',
                file: '上傳圖片',
                url: '音訊網址'
            },
            browser: {
                tags: '標籤',
                search: '搜尋',
            },
            caption: '標題',
            close: '取消',
            submitButton: '確定',
            revertButton: '恢復',
            proportion: '比例',
            basic: '基本',
            left: '左',
            right: '右',
            center: '居中',
            width: '寬度',
            height: '高度',
            size: '尺寸',
            ratio: '比'
        },
        controller: {
            edit: '編輯',
            unlink: '去除連結',
            remove: '刪除',
            insertRowAbove: '在上方插入',
            insertRowBelow: '在下方插入',
            deleteRow: '刪除行',
            insertColumnBefore: '在左側插入',
            insertColumnAfter: '在右側插入',
            deleteColumn: '刪除列',
            fixedColumnWidth: '固定列寬',
            resize100: '放大 100%',
            resize75: '放大 75%',
            resize50: '放大 50%',
            resize25: '放大 25%',
            mirrorHorizontal: '翻轉左右',
            mirrorVertical: '翻轉上下',
            rotateLeft: '向左旋轉',
            rotateRight: '向右旋轉',
            maxSize: '最大尺寸',
            minSize: '最小尺寸',
            tableHeader: '表格標題',
            mergeCells: '合併單元格',
            splitCells: '分割單元格',
            HorizontalSplit: '水平分割',
            VerticalSplit: '垂直分割'
        },
        menu: {
            spaced: '間隔開',
            bordered: '邊界線',
            neon: '霓虹燈',
            translucent: '半透明',
            shadow: '陰影',
            code: '程式碼'
        }
    };

    if (typeof noGlobal === typeof undefined) {
        if (!window.SUNEDITOR_LANG) {
            Object.defineProperty(window, 'SUNEDITOR_LANG', {
                enumerable: true,
                writable: false,
                configurable: false,
                value: {}
            });
        }

        Object.defineProperty(window.SUNEDITOR_LANG, 'zh_tw', {
            enumerable: true,
            writable: true,
            configurable: true,
            value: lang
        });
    }

    return lang;
}));
