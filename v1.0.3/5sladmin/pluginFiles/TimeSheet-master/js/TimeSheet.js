/**
 * TimeSheet.js [v1.0]
 * Li Bin
 */

(function ($) {
var colsForMb = [];
    /*
    * 表格中的單元格類
    * */
    var CSheetCell = function(opt){

        /*
         * opt : {
         *   state : 0 or 1,
         *   toggleCallback : function(curState){...}
         *   settingCallback : function(){...}
         * }
         *
         * */

        var cellPrivate = $.extend({
            state : 0,
            toggleCallback : false,
            settingCallback : false
        },opt);


        /*反向切換單元格狀態*/
        this.toggle = function(){
            cellPrivate.state = cellPrivate.state>0 ? cellPrivate.state-1 : cellPrivate.state+1;
            if(cellPrivate.toggleCallback){
                cellPrivate.toggleCallback(cellPrivate.state);
            }
        }

        /*
        * 設置單元格狀態
        * state : 0 or 1
        * */
        this.set = function(state){
            cellPrivate.state = state==0 ? 0 : 1;
            if(cellPrivate.settingCallback){
                cellPrivate.settingCallback();
            }
        }

        /*
        * 獲取單元格狀態
        * */
        this.get = function(){
            return cellPrivate.state;
        }

    }

    /*
    * 表格類
    * */
    var CSheet = function(opt){

        /*
        * opt : {
        *   dimensions : [8,9],  [行數，列數]
        *   sheetData : [[0,1,1,0,0],[...],[...],...]    sheet數據，二維數組，索引(a,b),a-行下標，b-列下標，每個cell只有0,1兩態，與dimensions對應
        *   toggleCallback : function(){..}
        *   settingCallback : function(){..}
        * }
        *
        * */

        var sheetPrivate = $.extend({
            dimensions : undefined,
            dayList : undefined,
            sheetData : undefined,
            testData : undefined,
            mbid : undefined,
            toggleCallback : false,
            settingCallback : false
        },opt);

        sheetPrivate.cells = [];
        //sheetPrivate.mbcols = [];

        /*
        * 初始化表格中的所有單元格
        * */
        sheetPrivate.initCells = function(){
            var rowNum = sheetPrivate.dimensions[0];
            var colNum = sheetPrivate.dimensions[1];
            if(sheetPrivate.dimensions.length==2 && rowNum>0 && colNum>0){
                for(var row= 0,curRow = []; row<rowNum; row++){
                    /* -- Jaz Fixed --  取得已選的時間格陣列 */
                    var checkArr = [];
                    var mbArr = {};
                    if(sheetPrivate.testData&&sheetPrivate.testData[sheetPrivate.dayList[row].name]){
                        var rowArr = sheetPrivate.testData[sheetPrivate.dayList[row].name]['value'];
                        for(var i=0; i<rowArr.length; i++){
                            for(var ai=rowArr[i][1][0]; ai<=rowArr[i][1][1]; ai++){
                                checkArr.push(ai);
                                mbArr[ai]=rowArr[i][2];
                            }
                        }
                    }
                    /* -- /Jaz Fixed -- */
                    var curRow = [];
                    var mbCol = [];
                    for(var col=0; col<colNum; ++col){
                        if($.inArray(col, checkArr)>=0){
                            curRow.push(new CSheetCell({ state : 1 }));/* -- Jaz Fixed -- */
                            mbCol.push(mbArr[col]);
                        } else {
                            curRow.push(new CSheetCell({ state : 0 }));/* -- Jaz Fixed -- */
                            mbCol.push(0);
                        }
                    }
                    sheetPrivate.cells.push(curRow);
                    colsForMb.push(mbCol);
                    //sheetOption.mbcol.push(mbCol);
                }
                //alert(colsForMb);
            }else{
                throw new Error("CSheet : wrong dimensions");
            }
        }
        /*
        * 初始化表格中的所有單元格
        * */
       /*
        sheetPrivate.initCells = function(){
            var rowNum = sheetPrivate.dimensions[0];
            var colNum = sheetPrivate.dimensions[1];
            if(sheetPrivate.dimensions.length==2 && rowNum>0 && colNum>0){
                for(var row= 0,curRow = []; row<rowNum; ++row){
                    curRow = [];
                    for(var col=0; col<colNum; ++col){
                        curRow.push(new CSheetCell({
                            state : sheetPrivate.sheetData ? (sheetPrivate.sheetData[row]?parseInt(sheetPrivate.sheetData[row][col]):0) : 0
                        }));
                    }
                    sheetPrivate.cells.push(curRow);
                }
            }else{
                throw new Error("CSheet : wrong dimensions");
            }
        }
        */
        /*
        * 對給定的表各區域進行 toggle 或 set操作
        * */
        sheetPrivate.areaOperate = function(area,opt){

            /*
             * area : {
             *   startCell : [2,1],
             *   endCell : [7,6]
             * }
             * opt : {
             *    type:"set" or "toggle",
             *    state : 0 or 1 if type is set
             * }
             * */

            var rowCount = sheetPrivate.cells.length;
            var colCount = sheetPrivate.cells[0] ? sheetPrivate.cells[0].length : 0;
            var operationArea = $.extend({
                startCell : [0,0],
                endCell : [rowCount-1,colCount-1]
            },area);

            var isSheetEmpty = rowCount==0 || colCount==0;
            var isAreaValid = operationArea.startCell[0]>=0 && operationArea.endCell[0]<=rowCount-1 &&
                operationArea.startCell[1]>=0 && operationArea.endCell[1]<=colCount-1 &&                                             //operationArea不能超越sheet的邊界
                operationArea.startCell[0]<=operationArea.endCell[0] && operationArea.startCell[1]<=operationArea.endCell[1];      //startCell必須居於endCell的左上方，或與之重合

            if(!isAreaValid){
                throw new Error("CSheet : operation area is invalid");
            }else if(!isSheetEmpty){
                for(var row=operationArea.startCell[0]; row<=operationArea.endCell[0]; ++row){
                    for(var col=operationArea.startCell[1]; col<=operationArea.endCell[1]; ++col){
                        if(opt.type=="toggle"){
                            sheetPrivate.cells[row][col].toggle();
                        }else if(opt.type=="set"){
                            sheetPrivate.cells[row][col].set(opt.state);
                        }
                    }
                }
            }

        }

        sheetPrivate.initCells();

        /*
        * 對錶格的指定區域進行狀態反向切換
        * toggleArea : {
         *   startCell : [2,1],
         *   endCell : [7,6]
         * }
        *
        * */
        this.toggle = function(toggleArea){

            sheetPrivate.areaOperate(toggleArea,{type:"toggle"});
            if(sheetPrivate.toggleCallback){
                sheetPrivate.toggleCallback();
            }

        }

        /*
        *  對錶格的指定區域進行狀態設置
        *  state : 0 or 1
        *  settingArea : {
         *   startCell : [2,1],
         *   endCell : [7,6]
         * }
        * */
        this.set = function(state,settingArea){

            sheetPrivate.areaOperate(settingArea,{type:"set",state:state});
            if(sheetPrivate.settingCallback){
                sheetPrivate.settingCallback();
            }
        }

        /*
         *  獲取指定單元格的狀態
         *  cellIndex ： [2,3]
         *  @return : 0 or 1
         * */
        this.getCellState = function(cellIndex){
            return sheetPrivate.cells[cellIndex[0]][cellIndex[1]].get();
        }

        /*
         *  獲取指定行所有單元格的狀態
         *  row ： 2
         *  @return : [1,0,...,1]
         * */
        this.getRowStates = function(row){
            var rowStates = [];
            for(var col=0; col<sheetPrivate.dimensions[1]; ++col){
                rowStates.push(sheetPrivate.cells[row][col].get());
            }
            return rowStates;
        }

        /*
         *  獲取所有單元格的狀態
         *  @return : [[1,0,...,1],[1,0,...,1],...,[1,0,...,1]]
         * */
        this.getSheetStates = function(){
            var sheetStates = [];
            for(var row= 0,rowStates = []; row<sheetPrivate.dimensions[0]; ++row){
                rowStates = [];
                for(var col=0; col<sheetPrivate.dimensions[1]; ++col){
                    var numStates = sheetPrivate.cells[row][col].get();
                    if(isNaN(numStates)==true){ numStates = 0; }
                    rowStates.push(numStates);
                }
                sheetStates.push(rowStates);
            }
            return sheetStates;
        }

    }
    


    $.fn.TimeSheet = function(opt){

        /*
         *   說明 ：
         *
         *   TimeSheet 應該被綁定在 TBODY 元素上，其子元素有如下默認class:
         *
         *   表頭 ---- class: .TimeSheet-head
         *   列表頭 ---- class: .TimeSheet-colHead
         *   行表頭 ---- class: .TimeSheet-rowHead
         *   單元格 ---- class: .TimeSheet-cell
         *
         *   用戶可在傳入的sheetClass下將元素的默認樣式覆蓋
         *   sheetClass將被賦予 TBODY 元素
         *
         *
         * opt :
         * {
         *      data : {
         *          dimensions : [7,8],
         *          colHead : [{name:"name1",title:"",style:"width,background,color,font"},{name:"name2",title:"",style:"width,background,color,font"},...]
         *          rowHead : [{name:"name1",title:"",style:"height,background,color,font"},{name:"name2",title:"",style:"height,background,color,font"},...]
         *          sheetHead : {name:"headName",style:"width,height,background,color,font"}
         *          sheetData : [[0,1,1,0,0],[...],[...],...]    sheet數據，二維數組，行主序，索引(a,b),a-行下標，b-列下標，每個cell只有0,1兩態，與dimensions對應
         *      },
         *
         *      sheetClass : "",
         *      start : function(ev){...}
<<<<<<< HEAD
         *      end : function(ev, selectedArea){...}
=======
         *      end : function(ev,selectedArea){...}
>>>>>>> b2f70472ecb97bbf9ecdc7f205ea10c8e5e7b929
         *      remarks : false
         * }
         *
         */

        var thisSheet = $(this);

        if(!thisSheet.is("TBODY")){
            throw new Error("TimeSheet needs to be bound on a TBODY element");
        }


        var sheetOption = $.extend({
            data: {},
            sheetClass: "",
            start: false,
            end : false,
            remarks : null
        }, opt);

        //alert(sheetOption.data.hourrange[0]);//colHead sheetOption.mbcol

        if(!sheetOption.data.dimensions || sheetOption.data.dimensions.length!==2 || sheetOption.data.dimensions[0]<0 || sheetOption.data.dimensions[1]<0){
            throw new Error("TimeSheet : wrong dimensions");
        }

        var operationArea = {
            startCell : undefined,
            endCell : undefined
        };

        var sheetModel = new CSheet({
            dimensions : sheetOption.data.dimensions,
            dayList : sheetOption.data.dayList ? sheetOption.data.dayList : undefined,
            sheetData : sheetOption.data.sheetData ? sheetOption.data.sheetData : undefined,
            testData : sheetOption.data.testData ? sheetOption.data.testData : undefined,
            mbid : sheetOption.data.mbid ? sheetOption.data.mbid : undefined
        });

        /*
        * 表格初始化
        * */
        var initSheet = function(){
            thisSheet.html("");
            thisSheet.addClass("TimeSheet");
            if(sheetOption.sheetClass){
                thisSheet.addClass(sheetOption.sheetClass);
            }
            initColHeads();
            initRows();
            repaintSheet();
        };

        var getWeekDay = function(nN,typE=''){
            var weekDay = [];
            if(typE==='s') { weekDay = ["日","一","二","三","四","五","六"]; }
            else if(typE==='f') { weekDay = ["星期日","星期一","星期二","星期三","星期四","星期五","星期六"]; }
            else { weekDay = ["(日)","(一)","(二)","(三)","(四)","(五)","(六)"]; }
            return weekDay[parseInt(nN)];
        };

        /*
        * 初始化每一列的頂部表頭
        * */

        var initColHeads = function(){
            /* ---------------- 頂行上半 -------------------- */
            var colHeadTopHtml = '<tr>';
            for(var i=0,curColHeadTop=''; i<=sheetOption.data.dimensions[1]; ++i){
                if(i===0){
                    curColHeadTop = '<td rowspan=2 class="diagonal-line TimeSheet-head" style="'+(sheetOption.data.sheetHead.style?sheetOption.data.sheetHead.style:'')+'">';
                    //curColHeadTop += sheetOption.data.sheetHead.name;
                    curColHeadTop += '<div class="container">';
                    curColHeadTop += '<div class="row justify-content-end"><div class="col-6">'+sheetOption.data.sheetHead.uppername+'</div></div>';
                    curColHeadTop += '<div class="row"><div class="col-6">'+sheetOption.data.sheetHead.lowername+'</div></div>';
                    curColHeadTop += '</div>';
                    curColHeadTop += '</td>';
                }else{
                    if(i%2===0){
                        continue;
                    } else {
                        var timeTop = sheetOption.data.colHead[i-1].name;
                        var sptime = timeTop.split(":");
                        timeTop = sptime[0]+'<span style="font-size:75%;">:00</span>';
                        curColHeadTop = '<td colspan="2" class="TimeSheet-colTop" title="'+(sheetOption.data.colHead[i-1].title ? sheetOption.data.colHead[i-1].title:"")+'" data-col="'+(i-1)+'" class="TimeSheet-colHead'+(i===sheetOption.data.dimensions[1]?' rightMost':'')+'" style="'+(sheetOption.data.colHead[i-1].style ? sheetOption.data.colHead[i-1].style : '')+'">'+timeTop+'</td>';
                    }
                }
                colHeadTopHtml += curColHeadTop;
            }
            colHeadTopHtml += '</tr>';
            /* --------------- 頂行下半 --------------------- */
            var colHeadHtml = '<tr>';
            for(var i=0,curColHead=''; i<=sheetOption.data.dimensions[1]; ++i){
                if(i===0){
                    continue;
                    //curColHead = '<td class="TimeSheet-head" style="'+(sheetOption.data.sheetHead.style?sheetOption.data.sheetHead.style:'')+'"></td>';
                }else{
                    //curColHead = '<td title="'+(sheetOption.data.colHead[i-1].title ? sheetOption.data.colHead[i-1].title:"")+'" data-col="'+(i-1)+'" class="TimeSheet-colHead '+(i===sheetOption.data.dimensions[1]?'rightMost':'')+'" style="'+(sheetOption.data.colHead[i-1].style ? sheetOption.data.colHead[i-1].style : '')+'">'+sheetOption.data.colHead[i-1].name+'</td>';
                    var mint = (i%2===0)?'30':'00';
                    curColHead = '<td title="'+(sheetOption.data.colHead[i-1].title ? sheetOption.data.colHead[i-1].title:"")+'" data-col="'+(i-1)+'" class="TimeSheet-colHead'+(i===sheetOption.data.dimensions[1]?' rightMost':'')+'" style="'+(sheetOption.data.colHead[i-1].style ? sheetOption.data.colHead[i-1].style : '')+'">'+mint+'</td>';
                }
                colHeadHtml += curColHead;
            }
            /*
            if(sheetOption.remarks){
                colHeadHtml += '<td class="TimeSheet-remarkHead">'+sheetOption.remarks.title+'</td>';
            }
            */
            colHeadHtml += '</tr>';
            thisSheet.append(colHeadTopHtml);
            thisSheet.append(colHeadHtml);
        };
       /*
        var initColHeads = function(){
            var colHeadHtml = '<tr>';
            for(var i=0,curColHead=''; i<=sheetOption.data.dimensions[1]; ++i){
                if(i===0){
                    curColHead = '<td class="TimeSheet-head" style="'+(sheetOption.data.sheetHead.style?sheetOption.data.sheetHead.style:'')+'">'+sheetOption.data.sheetHead.name+'</td>';
                }else{
                    curColHead = '<td title="'+(sheetOption.data.colHead[i-1].title ? sheetOption.data.colHead[i-1].title:"")+'" data-col="'+(i-1)+'" class="TimeSheet-colHead '+(i===sheetOption.data.dimensions[1]?'rightMost':'')+'" style="'+(sheetOption.data.colHead[i-1].style ? sheetOption.data.colHead[i-1].style : '')+'">'+sheetOption.data.colHead[i-1].name+'</td>';
                }
                colHeadHtml += curColHead;
            }

            if(sheetOption.remarks){
                colHeadHtml += '<td class="TimeSheet-remarkHead">'+sheetOption.remarks.title+'</td>';
            }
            colHeadHtml += '</tr>';
            thisSheet.append(colHeadHtml);
        };
        */
        /*
        * 初始化每一行
        * */
        /* -- 最小間隔半小時 -- */
        var initRows = function(){
            var cudate = new Date();
            var currDate = cudate.getFullYear() + '-' + (cudate.getMonth()+1).toString().padStart(2, '0') + '-' + cudate.getDate().toString().padStart(2, '0');
            for(var row=0,curRowHtml=''; row<sheetOption.data.dimensions[0]; ++row){
                curRowHtml='<tr class="TimeSheet-row'+((currDate===sheetOption.data.rowHead[row].name) ? ' currDate' : '')+'">';
                for(var col= 0, curCell=''; col<=sheetOption.data.dimensions[1]; ++col){
                    if(col===0){
                        curCell = '<td title="'+(sheetOption.data.rowHead[row].title ? sheetOption.data.rowHead[row].title:"")+'" class="TimeSheet-rowHead'+(6===sheetOption.data.rowHead[row].weeknum?' sat':'')+(0===sheetOption.data.rowHead[row].weeknum?' sun':'')+(row===sheetOption.data.dimensions[0]-1?' bottomMost':'')+'" style="'+(sheetOption.data.rowHead[row].style ? sheetOption.data.rowHead[row].style : '')+'">'+sheetOption.data.rowHead[row].name + ' ' + getWeekDay(sheetOption.data.rowHead[row].weeknum)+'</td>';
                    }else{
                        curCell = '<td class="TimeSheet-cell'+((col%2===0)?' half':'')+(row===sheetOption.data.dimensions[0]-1?' bottomMost':'')+(col===sheetOption.data.dimensions[1]?' rightMost':'')+'" data-row="'+row+'" data-col="'+(col-1)+'">'+((colsForMb[row][col-1]>0)?'<span style="color:#FFFFFF">'+colsForMb[row][col-1]+'</span>':'')+'</td>';
                    }
                    curRowHtml += curCell;
                }
                /*
                if(sheetOption.remarks){
                    curRowHtml += '<td class="TimeSheet-remark '+(row===sheetOption.data.dimensions[0]-1?'bottomMost ':' ')+'">'+sheetOption.remarks.default+'</td>';
                }
                */
                curRowHtml += '</tr>';
                thisSheet.append(curRowHtml);
            }
        };


        /* -- 最小間隔一小時 -- 
        var initRows = function(){
            for(var row=0,curRowHtml=''; row<sheetOption.data.dimensions[0]; ++row){
                curRowHtml='<tr class="TimeSheet-row">'
                for(var col= 0, curCell=''; col<=sheetOption.data.dimensions[1]; ++col){
                    if(col===0){
                        curCell = '<td title="'+(sheetOption.data.rowHead[row].title ? sheetOption.data.rowHead[row].title:"")+'"class="TimeSheet-rowHead '+(row===sheetOption.data.dimensions[0]-1?'bottomMost ':' ')+'" style="'+(sheetOption.data.rowHead[row].style ? sheetOption.data.rowHead[row].style : '')+'">'+sheetOption.data.rowHead[row].name+'</td>';
                    }else{
                        curCell = '<td class="TimeSheet-cell '+(row===sheetOption.data.dimensions[0]-1?'bottomMost ':' ')+(col===sheetOption.data.dimensions[1]?'rightMost':'')+'" data-row="'+row+'" data-col="'+(col-1)+'"></td>';
                    }
                    curRowHtml += curCell;
                }
                if(sheetOption.remarks){
                    curRowHtml += '<td class="TimeSheet-remark '+(row===sheetOption.data.dimensions[0]-1?'bottomMost ':' ')+'">'+sheetOption.remarks.default+'</td>';
                }
                curRowHtml += '</tr>';
                thisSheet.append(curRowHtml);
            }
            
        };
        */

        /*
        * 比較兩個單元格誰更靠近左上角
        * cell1:[2,3]
        * cell2:[4,5]
        * @return:{
             topLeft : cell1,
             bottomRight : cell2
         }
        * */
        var cellCompare = function(cell1,cell2){  //check which cell is more top-left
            var sum1 = cell1[0] + cell1[1];
            var sum2 = cell2[0] + cell2[1];

            if((cell1[0]-cell2[0])*(cell1[1]-cell2[1])<0){
                return {
                    topLeft : cell1[0]<cell2[0] ? [cell1[0],cell2[1]] : [cell2[0],cell1[1]],
                    bottomRight : cell1[0]<cell2[0] ? [cell2[0],cell1[1]] : [cell1[0],cell2[1]]
                };
            }

            return {
                topLeft : sum1<=sum2 ? cell1 : cell2,
                bottomRight : sum1>sum2 ? cell1 : cell2
            };
        };

        /*
        * 刷新表格
        * */
        var repaintSheet = function(){
            var sheetStates = sheetModel.getSheetStates();
            thisSheet.find(".TimeSheet-row").each(function(row,rowDom){
                var curRow = $(rowDom);
                curRow.find(".TimeSheet-cell").each(function(col,cellDom){
                    var curCell = $(cellDom);
                    if(sheetStates[row][col]===1){
                        curCell.addClass("TimeSheet-cell-selected");
                    }else if(sheetStates[row][col]===0){
                        curCell.removeClass("TimeSheet-cell-selected");
                    }
                });
            });
        };

        /*
        * 移除所有單元格的 TimeSheet-cell-selecting 類
        * */
        var removeSelecting = function(){
            thisSheet.find(".TimeSheet-cell-selecting").removeClass("TimeSheet-cell-selecting");
        };

        /*
        * 清空備註欄
        * */
        var cleanRemark = function(){
            thisSheet.find(".TimeSheet-remark").each(function(idx,ele){
                var curDom = $(ele);
                curDom.prop("title","");
                curDom.html(sheetOption.remarks.default);
            });
        };

        /*
        * 鼠標開始做選擇操作
        * startCel ： [1,4]
        * */
        var startSelecting = function(ev,startCel){
            operationArea.startCell = startCel;
            if(sheetOption.start){
                sheetOption.start(ev);
            }
        };

        /*
         * 鼠標在選擇操作過程中
         * topLeftCell ： [1,4]，      鼠標選擇區域的左上角
         * bottomRightCell ： [3,9]      鼠標選擇區域的右下角
         * */
        var duringSelecting = function(ev,topLeftCell,bottomRightCell){
            var curDom = $(ev.currentTarget);

            if(isSelecting && curDom.hasClass("TimeSheet-cell") || isColSelecting && curDom.hasClass("TimeSheet-colHead")){
                removeSelecting();
                for(var row=topLeftCell[0]; row<=bottomRightCell[0]; ++row){
                    for(var col=topLeftCell[1]; col<=bottomRightCell[1]; ++col){
                        $($(thisSheet.find(".TimeSheet-row")[row]).find(".TimeSheet-cell")[col]).addClass("TimeSheet-cell-selecting");
                    }
                }
            }
        };

        /*
         * 選擇操作完成後
         * targetArea ： {
         *      topLeft ： [1,2],
         *      bottomRight: [3,8]
         * }
         * */
        var afterSelecting = function(ev,targetArea){
            var curDom = $(ev.currentTarget);
            var key = $(ev.which);
            var targetState = undefined;

            if(key[0]===1){//鼠標左鍵,將選定區域置1
                //alert(sheetModel.getCellState(operationArea.startCell));
                //targetState = 1;
                targetState = sheetModel.getCellState(operationArea.startCell)===1 ? 0 : 1;
                //alert(thisSheet.undelegate());
            }
            else if(key[0]===3){//鼠標右鍵,將選定區域置0
                //targetState = 0;
                targetState = 1;
               if(sheetOption.data.mbid>0){alert('Open Dialog Window!');}
            }   

            if(isSelecting && curDom.hasClass("TimeSheet-cell") || isColSelecting && curDom.hasClass("TimeSheet-colHead")){
                sheetModel.set(targetState,{
                    startCell : targetArea.topLeft,
                    endCell   : targetArea.bottomRight
                });
                removeSelecting();
                repaintSheet();
                if(sheetOption.end){
                    sheetOption.end(ev,targetArea);
                }
            }else{
                removeSelecting();
            }

            isSelecting = false;
            isColSelecting = false;
            operationArea = {
                startCell : undefined,
                endCell : undefined
            }
        };

        var isSelecting = false;  /*鼠標在表格區域做選擇*/

        var isColSelecting = false; /*鼠標在列表頭區域做選擇*/

        var eventBinding = function(){

            /*防止重複綁定*/
            thisSheet.undelegate(".umsSheetEvent");

            /*表格開始選擇*/
            thisSheet.delegate(".TimeSheet-cell","mousedown.umsSheetEvent",function(ev){
                var curCell = $(ev.currentTarget);
                var startCell = [curCell.data("row"),curCell.data("col")];
                isSelecting = true;
                startSelecting(ev,startCell);
            });

            /*表格選擇完成*/
            thisSheet.delegate(".TimeSheet-cell","mouseup.umsSheetEvent",function(ev){
                if(!operationArea.startCell){
                    return;
                }
                var curCell = $(ev.currentTarget);
                var endCell = [curCell.data("row"),curCell.data("col")];
                var correctedCells = cellCompare(operationArea.startCell,endCell);
                afterSelecting(ev,correctedCells);
            });

            /*表格正在選擇*/
            thisSheet.delegate(".TimeSheet-cell","mouseover.umsSheetEvent",function(ev){
                if(!isSelecting){
                    return;
                }
                var curCell = $(ev.currentTarget);
                var curCellIndex = [curCell.data("row"),curCell.data("col")];
                var correctedCells = cellCompare(operationArea.startCell,curCellIndex);
                var topLeftCell = correctedCells.topLeft;
                var bottomRightCell = correctedCells.bottomRight;

                duringSelecting(ev,topLeftCell,bottomRightCell);
            });


            /*列表頭開始選擇*/
            thisSheet.delegate(".TimeSheet-colHead","mousedown.umsSheetEvent",function(ev){
                var curColHead = $(ev.currentTarget);
                var startCell = [0,curColHead.data("col")];
                isColSelecting = true;
                startSelecting(ev,startCell);
            });

            /*列表頭選擇完成*/
            thisSheet.delegate(".TimeSheet-colHead","mouseup.umsSheetEvent",function(ev){
                if(!operationArea.startCell){
                    return;
                }
                var curColHead = $(ev.currentTarget);
                var endCell = [sheetOption.data.dimensions[0]-1,curColHead.data("col")];
                var correctedCells = cellCompare(operationArea.startCell,endCell);
                afterSelecting(ev,correctedCells);
            });

            /*列表頭正在選擇*/
            thisSheet.delegate(".TimeSheet-colHead","mouseover.umsSheetEvent",function(ev){
                if(!isColSelecting){
                    return;
                }
                var curColHead = $(ev.currentTarget);
                var curCellIndex = [sheetOption.data.dimensions[0]-1,curColHead.data("col")];
                var correctedCells = cellCompare(operationArea.startCell,curCellIndex);
                var topLeftCell = correctedCells.topLeft;
                var bottomRightCell = correctedCells.bottomRight;

                duringSelecting(ev,topLeftCell,bottomRightCell);
            });

            /*表格禁止鼠標右鍵菜單*/
            thisSheet.delegate("td","contextmenu.umsSheetEvent",function(ev){
                return false;
            });
        };


        initSheet();
        eventBinding();


        var publicAPI = {

            /*
            * 獲取單元格狀態
            * cellIndex ：[1,2]
            * @return : 0 or 1
            * */
            getCellState : function(cellIndex){
                return sheetModel.getCellState(cellIndex);
            },

            /*
             * 獲取某行所有單元格狀態
             * row ：2
             * @return : [1,0,0,...,0,1]
             * */
            getRowStates : function(row){
                return sheetModel.getRowStates(row);
            },

            /*
             * 獲取表格所有單元格狀態
             * @return : [[1,0,0,...,0,1],[1,0,0,...,0,1],...,[1,0,0,...,0,1]]
             * */
            getSheetStates : function(){
                return sheetModel.getSheetStates();
            },

            /*
            * 設置某行的說明文字
            * row : 2,
            * html : 說明
            * */
            setRemark : function(row,html){
                if($.trim(html)!==''){
                    $(thisSheet.find(".TimeSheet-row")[row]).find(".TimeSheet-remark").prop("title",html).html(html);
                }
            },

            /*
            * 重置表格
            * */
            clean : function(){
                sheetModel.set(0,{});
                repaintSheet();
                cleanRemark();
            },

            /*
            * 獲取 default remark
            * */
            getDefaultRemark : function(){
                return sheetOption.remarks.default;
            },

            /*
            * 使表格不可操作
            * */
            disable : function(){
                thisSheet.undelegate(".umsSheetEvent");
            },

            /*
             * 使表格可操作
             * */
            enable : function(){
                eventBinding();
            },

            /*
            * 判斷表格是否所有單元格狀態都是1
            * @return ： true or false
            * */
            isFull : function(){
                for(var row=0; row<sheetOption.data.dimensions[0]; ++row){
                    for(var col=0; col<sheetOption.data.dimensions[1]; ++col){
                        if(sheetModel.getCellState([row,col])===0){
                            return false;
                        }
                    }
                }
                return true;
            }
        };

        return publicAPI;

    }


})(jQuery);
