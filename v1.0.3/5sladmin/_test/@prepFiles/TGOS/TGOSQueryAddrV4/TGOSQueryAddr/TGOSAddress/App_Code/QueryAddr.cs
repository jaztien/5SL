using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Net;
using System.IO;
using System.Text;
using System.Xml;

/// <summary>
/// Summary description for QueryAddr
/// </summary>
public class QueryAddr {

    public QueryAddr ( ) {
        //
        // TODO: Add constructor logic here
        //
    }

    //TGOSQueryAddrAppId
    public static string QueryAddraAppId {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOSQueryAddrAppId"];

            } catch {
                return "";
            }

        }
    }

    //TGOSQueryAddrAPIKey
    public static string QueryAddrAPIKey {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOSQueryAddrAPIKey"];

            } catch {
                return "";
            }
        }
    }

    //TGOS_Query_Addr_Url
    public static string Query_Addr_Url {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOS_Query_Addr_Url"];

            } catch {
                return "";
            }
        }
    }

    ////////////////////////////////////////////////////////////////////

    //TGOSQueryGeoAddrAppId
    public static string QueryGeoAddrAppId {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOSQueryGeoAddrAppId"];

            } catch {
                return "";
            }

        }
    }

    //TGOSQueryGeoAddrAPIKey
    public static string QueryGeoAddrAPIKey {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOSQueryGeoAddrAPIKey"];

            } catch {
                return "";
            }
        }
    }

    //TGOS_Query_GeoAddr_Url
    public static string Query_GeoAddr_Url {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOS_Query_GeoAddr_Url"];

            } catch {
                return "";
            }
        }
    }

    ////////////////////////////////////////////////////////////////////


    //TGOSQueryAddrListAppId
    public static string QueryAddrListAppId {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOSQueryAddrListAppId"];

            } catch {
                return "";
            }

        }
    }

    //TGOSQueryAddrListAPIKey
    public static string QueryAddrListAPIKey {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOSQueryAddrListAPIKey"];

            } catch {
                return "";
            }
        }
    }

    //TGOS_Query_AddrList_Url
    public static string Query_AddrList_Url {
        get {
            try {
                return System.Web.Configuration.WebConfigurationManager.AppSettings["TGOS_Query_AddrList_Url"];

            } catch {
                return "";
            }
        }
    }

    ////////////////////////////////////////////////////////////////////

    public static string QueryAddrResult ( string oParam, string oUrl, string oLblResult ) {

        HttpWebRequest req = (HttpWebRequest)HttpWebRequest.Create (oUrl);
        oParam = oParam.Replace ("+", "%2B");
        //參數以UTF8格式轉成Byte[]之後才能夠寫入資料流
        byte[] PostData = Encoding.UTF8.GetBytes (oParam);
        //取得或設定要求的方法
        req.Method = "POST";
        //取得或設定 Content-type HTTP 標頭的值。 輸入資料被編碼為名稱/值對 
        //當Method為get時候，瀏覽器用x-www-form-urlencoded的編碼方式把form數據轉換成一個字串（name1=value1&name2=value2...）
        //然後把這個字串append到url後面，用?分割，加載這個新的url。
        //當Method為post時候，瀏覽器把form數據封裝到http body中，
        //然後發送到server。如果沒有type=file的控件，用默認的application/x-www-form-urlencoded就可以了。
        req.ContentType = "application/x-www-form-urlencoded";

        //取得或設定 Content-length HTTP 標頭(資訊)
        req.ContentLength = PostData.Length;
        //client對sever請求提出取得資料流 
        //using清理非托管不受GC控制的資源
        using ( Stream reqStream = req.GetRequestStream ( ) ) {
            //把參數資料寫入資料流
            reqStream.Write (PostData, 0, PostData.Length);
        }
        //取server端資料使用 request.GetResponse() 方法時，
        //HttpWebRequest即會將資料傳送到指定的網址做處理，
        //處理完成後會依照Server的動作看是回傳資料或是重新指向他頁
        using ( WebResponse wr = req.GetResponse ( ) ) {

            //取得sever端資料流放入myStream
            //一般回傳'所需要的資料要用 HttpWebResponse類別 來接收Response的內容，
            //取得Response後在使用 StreamReader類別 來讀取 response.GetResponseStream() 的資料流內容
            using ( Stream myStream = wr.GetResponseStream ( ) ) {
                //new一個新的資料讀取器,從myStream取得資料
                //清理非托管不受GC控制的資源
                using ( StreamReader myStreamReader = new StreamReader (myStream) ) {

                    //對讀取出來的JSON格式資料進行加密
                    string Json = HttpUtility.HtmlEncode (myStreamReader.ReadToEnd ( ));
                    //對讀取出來的JSON格式資料進行解密
                    Json = HttpContext.Current.Server.HtmlDecode (Json);
                    //XmlDocument讀取XML
                    XmlDocument aXmlDocument = new XmlDocument ( );
                    aXmlDocument.LoadXml (Json);

                    //使用 StreamReader 來從標準文字檔讀取資料。
                    //HttpUtility.HtmlEncode()將字串轉換為 HTML 編碼的字串。
                    return oLblResult = aXmlDocument.DocumentElement.InnerText;

                }
            }
        }

    }

    public static string GeoQueryAddrResult ( string oParam, string oUrl, string oLblResult ) {



        HttpWebRequest req = (HttpWebRequest)HttpWebRequest.Create (oUrl);
        oParam = oParam.Replace ("+", "%2B");
        byte[] PostData = Encoding.UTF8.GetBytes (oParam);

        req.Method = "POST";//取得或設定要求的方法
        req.ContentType = "application/x-www-form-urlencoded";


        req.ContentLength = PostData.Length;//取得或設定 Content-length HTTP 標頭(資訊)

        using ( Stream reqStream = req.GetRequestStream ( ) ) {
            //client對sever請求提出取得資料流 
            reqStream.Write (PostData, 0, PostData.Length);//把參數資料寫入資料流
        }//清理非托管不受GC控制的资源

        using ( WebResponse wr = req.GetResponse ( ) ) {

            using ( Stream myStream = wr.GetResponseStream ( ) ) {

                using ( StreamReader myStreamReader = new StreamReader (myStream) )//new一個新的資料讀取器,從myStream取得資料
                {

                    //LblResultDataType.Text = aResultDataType;
                    //使用 StreamReader 來從標準文字檔讀取資料。
                    string Json = myStreamReader.ReadToEnd ( );
                    ////對讀取出來的JSON格式資料進行解密
                    Json = HttpUtility.HtmlDecode (Json);



                    return oLblResult = HttpUtility.HtmlEncode (Json);



                    //return oLblResult = HttpUtility.HtmlEncode(Json);//使用 StreamReader 來從標準文字檔讀取資料。
                    //HttpUtility.HtmlEncode()將字串轉換為 HTML 編碼的字串。

                }
            }
        }//清理非托管不受GC控制的资源

    }

    public static string GetAddrListResult ( string oparam, string oUrl ) {
        HttpWebRequest req = (HttpWebRequest)HttpWebRequest.Create (oUrl);
        oparam = oparam.Replace ("+", "%2B");
        byte[] PostData = Encoding.UTF8.GetBytes (oparam);

        req.Method = "POST";//取得或設定要求的方法
        req.ContentType = "application/x-www-form-urlencoded";


        req.ContentLength = PostData.Length;//取得或設定 Content-length HTTP 標頭(資訊)

        using ( Stream reqStream = req.GetRequestStream ( ) ) {
            //client對sever請求提出取得資料流 
            reqStream.Write (PostData, 0, PostData.Length);//把參數資料寫入資料流
        }//清理非托管不受GC控制的资源

        using ( WebResponse wr = req.GetResponse ( ) ) {

            using ( Stream myStream = wr.GetResponseStream ( ) ) {

                using ( StreamReader myStreamReader = new StreamReader (myStream) )//new一個新的資料讀取器,從myStream取得資料
                {
                    //使用 StreamReader 來從標準文字檔讀取資料。
                    string Json = myStreamReader.ReadToEnd ( );
                    ////對讀取出來的JSON格式資料進行解密


                    ////XmlDocument讀取XML
                    XmlDocument aXmlDocument = new XmlDocument ( );
                    aXmlDocument.LoadXml (Json);

                    return aXmlDocument.InnerText;


                }
            }
        }//清理非托管不受GC控制的资源

    }


}
