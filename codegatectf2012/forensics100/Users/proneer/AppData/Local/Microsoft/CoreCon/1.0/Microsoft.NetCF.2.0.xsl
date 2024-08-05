<?xml version="1.0" standalone="no"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
  <ADDONCONTAINER>
    <ADDON>
      <PACKAGECONTAINER>

      <!-- NETCF 2.0 SR for WindowsMobile platforms -->
      <PACKAGE Name=".netcf installable SR CAB" ID="C0CCF48E-4BFB-4d84-827C-981A595E40B4" Protected="true">
      <PROPERTYCONTAINER>
        <PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
      </PROPERTYCONTAINER>
      <PACKAGETYPECONTAINER>

 
        <!-- NETCF System Resource files for PPC03-->
        <PACKAGETYPE Name="ARMV4" ID="ARMV4" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce400\armv4\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>

          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>
        </PACKAGETYPE>

        <PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\armv4i\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko_wm.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>
        </PACKAGETYPE>
      </PACKAGETYPECONTAINER>
      </PACKAGE>

      <!-- NETCF 2.0 SR for CE5.0 platforms -->
      <PACKAGE Name=".netcf installable SR CAB" ID="C11F029E-E5B2-45ea-8E1E-2266F5CD025F" Protected="true">
      <PROPERTYCONTAINER>
        <PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
      </PROPERTYCONTAINER>
      <PACKAGETYPECONTAINER>
        <PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wc500\armv4i\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>
        </PACKAGETYPE>
        <PACKAGETYPE Name="SH4" ID="SH4" Protected="true">
        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\sh4\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">SH4</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>
        </PACKAGETYPE>
        <PACKAGETYPE Name="MIPSII" ID="MIPSII" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\mipsii\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">MIPSII</PROPERTY>
        </PROPERTYCONTAINER>
        <FILECONTAINER>
          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>
        <PACKAGETYPE Name="MIPSII_FP" ID="MIPSII_FP" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\mipsii\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">MIPSII_FP</PROPERTY>
        </PROPERTYCONTAINER>
        <FILECONTAINER>
          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>
        <PACKAGETYPE Name="MIPSIV" ID="MIPSIV" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\mipsiv\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">MIPSIV</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>
        <PACKAGETYPE Name="MIPSIV_FP" ID="MIPSIV_FP" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\mipsiv\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">MIPSIV_FP</PROPERTY>
        </PROPERTYCONTAINER>
        <FILECONTAINER>
          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>
        <PACKAGETYPE Name="X86" ID="X86" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\x86\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">X86</PROPERTY>
        </PROPERTYCONTAINER>
        <FILECONTAINER>
          <!-- NETCF System Resource files -->
          <FILE ID="System_SR_enu.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_chs.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_cht.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_de.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_es.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_fr.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_it.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ja.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
          <FILE ID="System_SR_ko.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>
        </PACKAGETYPE>
      </PACKAGETYPECONTAINER>
      </PACKAGE>

      <!-- NETCF 2.0 package for WindowsMobile PPC03 and PPC and SP 05  -->
      <PACKAGE Name=".netcf installable CAB" ID="ABD785F0-CDA7-41c5-8375-2451A7CBFF26" Protected="true">

      <PROPERTYCONTAINER>
        <PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
              <PROPERTY ID="DependentReferences">
                <PROPERTYCONTAINER>
                  <PROPERTY ID="1">
                    <PROPERTYCONTAINER>
                      <PROPERTY ID="Source" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\Diagnostics\System_SR_ENU.CAB</PROPERTY>
                      <PROPERTY ID="StrongName" Protected="true">System.SR, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac</PROPERTY>
                      <PROPERTY ID="Destination">\Windows\System.SR.Dll</PROPERTY>
                      <PROPERTY ID="ReferenceTypeFlags">RTF_IMPLICIT</PROPERTY>
                    </PROPERTYCONTAINER>
                  </PROPERTY>
                </PROPERTYCONTAINER>
              </PROPERTY>
      </PROPERTYCONTAINER>

      <PACKAGETYPECONTAINER>

        <PACKAGETYPE Name="ARMV4" ID="ARMV4" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce400\armv4\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <FILE ID="NETCFV2.ppc.armv4.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>
        <PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\armv4i\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <FILE ID="NETCFv2.wm.ARMV4I.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>

      </PACKAGETYPECONTAINER>

      </PACKAGE>

      <!-- NETCF 2.0 package for default Windows CE 5.x etc -->
      <PACKAGE Name=".netcf installable CAB" ID="E2BECB1F-8C8C-41ba-B736-9BE7D946A398" Protected="true">

      <PROPERTYCONTAINER>
        <PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
              <PROPERTY ID="DependentReferences">
                <PROPERTYCONTAINER>
                  <PROPERTY ID="1">
                    <PROPERTYCONTAINER>
                      <PROPERTY ID="Source" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\Diagnostics\System_SR_ENU.CAB</PROPERTY>
                      <PROPERTY ID="StrongName" Protected="true">System.SR, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac</PROPERTY>
                      <PROPERTY ID="Destination">\Windows\System.SR.Dll</PROPERTY>
                      <PROPERTY ID="ReferenceTypeFlags">RTF_IMPLICIT</PROPERTY>
                    </PROPERTYCONTAINER>
                  </PROPERTY>
                </PROPERTYCONTAINER>
              </PROPERTY>
      </PROPERTYCONTAINER>

      <PACKAGETYPECONTAINER>
        <PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\armv4i\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <FILE ID="NETCFV2.wce5.armv4i.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>

        <PACKAGETYPE Name="SH4" ID="SH4" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\sh4\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">SH4</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <FILE ID="NETCFV2.wce5.sh4.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>

        <PACKAGETYPE Name="MIPSII" ID="MIPSII" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\mipsii\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">MIPSII</PROPERTY>
        </PROPERTYCONTAINER>
        <FILECONTAINER>
          <FILE ID="NETCFV2.wce5.mipsii.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>

	  	<!-- MIPSII_FP uses the same CAB as MIPSII -->

        <PACKAGETYPE Name="MIPSII_FP" ID="MIPSII_FP" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\mipsii\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">MIPSII_FP</PROPERTY>
        </PROPERTYCONTAINER>
        <FILECONTAINER>
          <FILE ID="NETCFV2.wce5.mipsii.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>

        <PACKAGETYPE Name="MIPSIV" ID="MIPSIV" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\mipsiv\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">MIPSIV</PROPERTY>
        </PROPERTYCONTAINER>

        <FILECONTAINER>
          <FILE ID="NETCFV2.wce5.mipsiv.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>

	  	<!-- MIPSIV_FP uses the same CAB as MIPSIV -->

        <PACKAGETYPE Name="MIPSIV_FP" ID="MIPSIV_FP" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\mipsiv\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">MIPSIV_FP</PROPERTY>
        </PROPERTYCONTAINER>
        <FILECONTAINER>
          <FILE ID="NETCFV2.wce5.mipsiv.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>

        </PACKAGETYPE>

        <PACKAGETYPE Name="X86" ID="X86" Protected="true">

        <PROPERTYCONTAINER>
          <PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
          <PROPERTY ID="RootPath" _UseNetCFRelativePath="v2.0.0.0" Protected="true">windowsce\wce500\x86\</PROPERTY>
          <PROPERTY ID="CPU" Protected="true">X86</PROPERTY>
        </PROPERTYCONTAINER>
        <FILECONTAINER>
          <FILE ID="NETCFV2.wce5.x86.cab">
            <PROPERTYCONTAINER>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
        </FILECONTAINER>
        </PACKAGETYPE>

      </PACKAGETYPECONTAINER>

      </PACKAGE>

      </PACKAGECONTAINER>

      <FILECONTAINER>
        <FILE ID="mscoree, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile 05 PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile 05 SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="mscoree2_0, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Cgacutil.exe, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Netcfagl2_0, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Netcfd3dm2_0, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="mscorlib, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Drawing, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Messaging, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Web.Services, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Windows.Forms, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Windows.Forms.Datagrid, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Xml, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Net.IrDA, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Data, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>

        <FILE ID="Microsoft.VisualBasic, Version=8.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Microsoft.WindowsCE.Forms, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="CustomMarshalers.dll, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Microsoft.WindowsMobile.DirectX, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF26</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.SR, Version=2.0.0.0, Culture=neutral, PublicKeyToken=969db8053d3322ac">
            <PROPERTYCONTAINER>
              <!-- default platform maps to the Windows CE package -->
              <PROPERTY ID="default">C11F029E-E5B2-45ea-8E1E-2266F5CD025F</PROPERTY>
              <PROPERTY ID="E2BECB1F-8C8C-41BA-B736-9BE7D946A398">C11F029E-E5B2-45ea-8E1E-2266F5CD025F</PROPERTY>
              <!-- PPC03 platform maps to the WindowsMobile Package -->
              <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">C0CCF48E-4BFB-4d84-827C-981A595E40B4</PROPERTY>
              <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
              <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">C0CCF48E-4BFB-4d84-827C-981A595E40B4</PROPERTY>
              <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
              <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">C0CCF48E-4BFB-4d84-827C-981A595E40B4</PROPERTY>
              <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
              <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">C0CCF48E-4BFB-4d84-827C-981A595E40B4</PROPERTY>
              <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
              <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">C0CCF48E-4BFB-4d84-827C-981A595E40B4</PROPERTY>
            </PROPERTYCONTAINER>
        </FILE>
      </FILECONTAINER>
    </ADDON>
  </ADDONCONTAINER>
</xsl:template>
</xsl:stylesheet>
