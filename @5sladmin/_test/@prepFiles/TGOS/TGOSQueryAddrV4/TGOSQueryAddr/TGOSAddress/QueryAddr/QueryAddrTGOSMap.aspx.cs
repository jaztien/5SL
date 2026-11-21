using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Net;
using System.Text;
using System.IO;
using System.Xml;
using System.Data;

public partial class Sample_Codes_TGOSQueryAddr_Sample02 : System.Web.UI.Page
{
    protected void Page_Load ( object sender, EventArgs e ) {
        if ( !IsPostBack ) {
            Query ( );
        }
    }

    protected void BtnQuery_Click ( object sender, EventArgs e ) {
        Query ( );
    }

    private void Query ( ) {


        //所要查詢的門牌地址
        string aAddress = TxtAddress.Text;
        //坐標系統(SRS)EPSG:4326(WGS84)國際通用, EPSG:3825 (TWD97TM119) 澎湖及金馬適用,EPSG:3826 (TWD97TM121) 台灣地區適用,EPSG:3827 (TWD67TM119) 澎湖及金馬適用,EPSG:3828 (TWD67TM121) 台灣地區適用
        string aSRS = "EPSG:3826";
        //0:最近門牌號機制,1:單雙號機制,2:[最近門牌號機制]+[單雙號機制]
        int aFuzzyType = 0;
        //回傳的資料格式，允許傳入的代碼為：JSON、XML
        string aResultDataType = "JSON";
        //模糊比對回傳門牌號的許可誤差範圍，輸入格式為正整數，如輸入 0 則代表不限制誤差範圍
        int aFuzzyBuffer = 0;
        //是否只進行完全比對，允許傳入的值為：true、false，如輸入 true ，模糊比對機制將不被使用
        string aIsOnlyFullMatch = "false";
        //是否支援舊門牌的查詢，允許傳入的值為：true、false，如輸入 true ，查詢時範圍包含舊門牌
        string aIsSupportHistory = "true";
        //是否鎖定縣市，允許傳入的值為：true、false，如輸入 true ，則代表查詢結果中的 [縣市] 要與所輸入的門牌地址中的 [縣市] 完全相同
        string aIsLockCounty = "false";
        //是否鎖定鄉鎮市區，允許傳入的值為：true、false，如輸入 true ，則代表查詢結果中的 [鄉鎮市區] 要與所輸入的門牌地址中的 [鄉鎮市區] 完全相同
        string aIsLockTown = "false";
        //是否鎖定村里，允許傳入的值為：true、false，如輸入 true ，則代表查詢結果中的 [村里] 要與所輸入的門牌地址中的 [村里] 完全相同
        string aIsLockVillage = "false";
        //是否鎖定路段，允許傳入的值為：true、false，如輸入 true ，則代表查詢結果中的 [路段] 要與所輸入的門牌地址中的 [路段] 完全相同
        string aIsLockRoadSection = "false";
        //是否鎖定巷，允許傳入的值為：true、false，如輸入 true ，則代表查詢結果中的 [巷] 要與所輸入的門牌地址中的 [巷] 完全相同
        string aIsLockLane = "false";
        //是否鎖定弄，允許傳入的值為：true、false，如輸入 true ，則代表查詢結果中的 [弄] 要與所輸入的門牌地址中的 [弄] 完全相同
        string aIsLockAlley = "false";
        //是否鎖定地區，允許傳入的值為：true、fals，如輸入 true ，則代表查詢結果中的 [地區] 要與所輸入的門牌地址中的 [地區] 完全相同
        string aIsLockArea = "false";
        //號之、之號是否視為相同，允許傳入的值為：true、false
        string aIsSameNumber_SubNumber = "false";
        //TGOS_Query_Addr_Url WebService網址
        string aCanIgnoreVillage = "true";
        //找不時是否可忽略村里，允許傳入的值為：true、false
        string aCanIgnoreNeighborhood = "true";
        //找不時是否可忽略鄰，允許傳入的值為：true、false
        int aReturnMaxCount = 0;
        //如為多筆時，限制回傳最大筆數，輸入格式為正整數，如輸入 0 則代表不限制回傳筆數
     
        //使用string.Format給予參數設定
        string param = @"oAPPId={0}&oAPIKey={1}&oAddress={2}&oSRS={3}&oFuzzyType={4}
                         &oResultDataType={5}&oFuzzyBuffer={6}&oIsOnlyFullMatch={7}&oIsSupportHistory={8}
                         &oIsLockCounty={9}&oIsLockTown={10}&oIsLockVillage={11}&oIsLockRoadSection={12}
                         &oIsLockLane={13}&oIsLockAlley={14}&oIsLockArea={15}&oIsSameNumber_SubNumber={16}
                         &oCanIgnoreVillage={17}&oCanIgnoreNeighborhood={18}&oReturnMaxCount={19}";

        //給予需要的參數
        param = string.Format (param,

            //應用程式識別碼(APPId) 實由使用者向TGOS申請
              QueryAddr.QueryAddraAppId,
            //應用程式介接驗證碼(APIKey)由使用者向TGOS申請
              QueryAddr.QueryAddrAPIKey,
              aAddress,
              aSRS,
              aFuzzyType,
              aResultDataType,
              aFuzzyBuffer,
              aIsOnlyFullMatch,
              aIsSupportHistory,
              aIsLockCounty,
              aIsLockTown,
              aIsLockVillage,
              aIsLockRoadSection,
              aIsLockLane,
              aIsLockAlley,
              aIsLockArea,
              aIsSameNumber_SubNumber,
              aCanIgnoreVillage,
              aCanIgnoreNeighborhood,
              aReturnMaxCount
          );

        LblResult.Text = QueryAddr.QueryAddrResult (param, QueryAddr.Query_Addr_Url + "/QueryAddr?", LblResult.Text);
       

    }
}