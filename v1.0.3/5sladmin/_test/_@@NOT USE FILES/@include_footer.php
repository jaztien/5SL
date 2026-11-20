<!-- ######################################################### -->
<!-- #################### 頁尾導航區嵌入檔案 #################### -->
<!-- ######################################################### -->
<footer class="main-footer text-sm">
    Copyright &copy; 2023 <strong><?php $getENname = $DB->getConfigVal('comp_enname');echo $getENname['config_value']; ?> </strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> <?php $getVersion = $DB->getConfigVal('sys_version'); echo $getVersion['config_value']; ?>
    </div>
</footer>
</script>
