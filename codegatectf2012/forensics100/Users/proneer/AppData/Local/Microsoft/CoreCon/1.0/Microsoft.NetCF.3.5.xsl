<?xml version="1.0" standalone="no"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">
<ADDONCONTAINER>
<ADDON>
<PACKAGECONTAINER>
<!-- NETCF 3.5 SR for WindowsMobile platforms -->
<PACKAGE Name=".netcf installable SR CAB" ID="C0CCF48E-4BFB-4d84-827C-981A595E40C5" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="NDPVersion" Protected="true">v3.5.0.0</PROPERTY>
</PROPERTYCONTAINER>
<PACKAGETYPECONTAINER>
<!-- NETCF System Resource files for PPC03-->
<PACKAGETYPE Name="ARMV4" ID="ARMV4" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

          <FILE ID="NETCFv35.Messages.EN.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.JA.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.DE.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.FR.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.IT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHS.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.KO.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.ES.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
</FILECONTAINER>
</PACKAGETYPE>
<PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

         <FILE ID="NETCFv35.Messages.EN.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>

         <FILE ID="NETCFv35.Messages.JA.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>

         <FILE ID="NETCFv35.Messages.DE.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>

         <FILE ID="NETCFv35.Messages.FR.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>

         <FILE ID="NETCFv35.Messages.IT.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>

         <FILE ID="NETCFv35.Messages.zh-CHT.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>

         <FILE ID="NETCFv35.Messages.zh-CHS.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>

         <FILE ID="NETCFv35.Messages.KO.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>

         <FILE ID="NETCFv35.Messages.ES.wm.cab" Protected="true">
           <PROPERTYCONTAINER>
             <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
             <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
             <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
             <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
           </PROPERTYCONTAINER>
         </FILE>
</FILECONTAINER>
</PACKAGETYPE>
</PACKAGETYPECONTAINER>
</PACKAGE>
<!-- NETCF 3.5 SR for CE5.0 platforms -->
<PACKAGE Name=".netcf installable SR CAB" ID="C11F029E-E5B2-45ea-8E1E-2266F5CD02560" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="NDPVersion" Protected="true">v3.5.0.0</PROPERTY>
</PROPERTYCONTAINER>
<PACKAGETYPECONTAINER>
<PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

          <FILE ID="NETCFv35.Messages.EN.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.JA.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.DE.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.FR.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.IT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHS.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.KO.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.ES.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
</FILECONTAINER>
</PACKAGETYPE>
<PACKAGETYPE Name="SH4" ID="SH4" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">SH4</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

          <FILE ID="NETCFv35.Messages.EN.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.JA.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.DE.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.FR.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.IT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHS.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.KO.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.ES.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
</FILECONTAINER>
</PACKAGETYPE>
<PACKAGETYPE Name="MIPSII" ID="MIPSII" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">MIPSII</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

          <FILE ID="NETCFv35.Messages.EN.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.JA.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.DE.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.FR.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.IT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHS.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.KO.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.ES.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
</FILECONTAINER>
</PACKAGETYPE>
<PACKAGETYPE Name="MIPSII_FP" ID="MIPSII_FP" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">MIPSII_FP</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

          <FILE ID="NETCFv35.Messages.EN.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.JA.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.DE.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.FR.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.IT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHS.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.KO.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.ES.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
</FILECONTAINER>
</PACKAGETYPE>
<PACKAGETYPE Name="MIPSIV" ID="MIPSIV" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">MIPSIV</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

          <FILE ID="NETCFv35.Messages.EN.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.JA.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.DE.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.FR.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.IT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHS.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.KO.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.ES.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
</FILECONTAINER>
</PACKAGETYPE>
<PACKAGETYPE Name="MIPSIV_FP" ID="MIPSIV_FP" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">MIPSIV_FP</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

          <FILE ID="NETCFv35.Messages.EN.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.JA.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.DE.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.FR.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.IT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHS.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.KO.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.ES.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
</FILECONTAINER>
</PACKAGETYPE>
<PACKAGETYPE Name="X86" ID="X86" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="RemotePath" Protected="true">\Windows\</PROPERTY>
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
<PROPERTY ID="CPU" Protected="true">X86</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<!-- NETCF System Resource files -->

          <FILE ID="NETCFv35.Messages.EN.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1033</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.JA.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1041</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.DE.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1031</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.FR.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1036</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.IT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1040</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHT.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1028</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.zh-CHS.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">2052</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.KO.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">1042</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>

          <FILE ID="NETCFv35.Messages.ES.cab" Protected="true">
            <PROPERTYCONTAINER>
              <PROPERTY ID="PackageSRValue" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\diagnostics\</PROPERTY>
              <PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
              <PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
              <PROPERTY ID="PackageLcidValue" Protected="true">3082</PROPERTY>
            </PROPERTYCONTAINER>
          </FILE>
</FILECONTAINER>
</PACKAGETYPE>
</PACKAGETYPECONTAINER>
</PACKAGE>
<!-- NETCF 3.5 package for WindowsMobile PPC03 and PPC and SP 05  -->
<PACKAGE Name=".netcf installable CAB" ID="ABD785F0-CDA7-41c5-8375-2451A7CBFF37" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="NDPVersion" Protected="true">v3.5.0.0</PROPERTY>
<PROPERTY ID="DependentReferences">
<PROPERTYCONTAINER>
<PROPERTY ID="1">
<PROPERTYCONTAINER>
<PROPERTY ID="Source" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\Diagnostics\NETCFv35.Messages.EN.cab</PROPERTY>
<PROPERTY ID="StrongName" Protected="true">System.SR, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC</PROPERTY>
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.ppc.armv4.cab">
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.wm.ARMV4I.cab">
<PROPERTYCONTAINER>
<PROPERTY ID="Installer">\Windows\wceload.exe</PROPERTY>
<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
</PROPERTYCONTAINER>
</FILE>
</FILECONTAINER>
</PACKAGETYPE>
</PACKAGETYPECONTAINER>
</PACKAGE>
<!-- NETCF 3.5 package for default Windows CE 5.x etc -->
<PACKAGE Name=".netcf installable CAB" ID="E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9" Protected="true">
<PROPERTYCONTAINER>
<PROPERTY ID="NDPVersion" Protected="true">v3.5.0.0</PROPERTY>
<PROPERTY ID="DependentReferences">
<PROPERTYCONTAINER>
<PROPERTY ID="1">
<PROPERTYCONTAINER>
<PROPERTY ID="Source" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce\Diagnostics\NETCFv35.Messages.EN.cab</PROPERTY>
<PROPERTY ID="StrongName" Protected="true">System.SR, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC</PROPERTY>
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.wce.armv4.cab">
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">SH4</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.wce.sh4.cab">
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">MIPSII</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.wce.mipsii.cab">
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">MIPSII_FP</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.wce.mipsii.cab">
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">MIPSIV</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.wce.mipsiv.cab">
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">MIPSIV_FP</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.wce.mipsiv.cab">
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
<PROPERTY ID="RootPath" _UseNetCFRelativePath="v3.5.0.0" Protected="true">windowsce</PROPERTY>
<PROPERTY ID="CPU" Protected="true">X86</PROPERTY>
</PROPERTYCONTAINER>
<FILECONTAINER>
<FILE ID="NETCFv35.wce.x86.cab">
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
        <FILE ID="mscorlib, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Drawing, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Messaging, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Web.Services, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Windows.Forms, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Xml, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Xml.Linq, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Net.Irda, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Data, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Data.DataSetExtensions, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Microsoft.VisualBasic, Version=8.1.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Microsoft.Windowsce.Forms, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Microsoft.WindowsMobile.DirectX, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="CustomMarshalers, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Core, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.ServiceModel, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="System.Runtime.Serialization, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Microsoft.ServiceModel.Channels.Mail, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
        <FILE ID="Microsoft.ServiceModel.Channels.Mail.WindowsMobile, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
          <PROPERTYCONTAINER>
            <!-- default platform maps to the Windows CE package -->
            <PROPERTY ID="default">E2BECB1F-8C8C-41ba-B736-9BE7D946A3A9</PROPERTY>
            <!-- PPC03 platform maps to the WindowsMobile Package -->
            <PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
            <PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- WindowsMobile SP platform maps to the WindowsMobile package -->
            <PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
            <PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
            <!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
            <PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">ABD785F0-CDA7-41c5-8375-2451A7CBFF37</PROPERTY>
          </PROPERTYCONTAINER>
        </FILE>
<FILE ID="System.SR, Version=3.5.0.0, Culture=neutral, PublicKeyToken=969DB8053D3322AC">
<PROPERTYCONTAINER>
<!-- default platform maps to the Windows CE package -->
<PROPERTY ID="default">C11F029E-E5B2-45ea-8E1E-2266F5CD02560</PROPERTY>
<!-- PPC03 platform maps to the WindowsMobile Package -->
<PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">C0CCF48E-4BFB-4d84-827C-981A595E40C5</PROPERTY>
<!-- WindowsMobile PPC platform maps to the WindowsMobile package -->
<PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">C0CCF48E-4BFB-4d84-827C-981A595E40C5</PROPERTY>
<!-- WindowsMobile SP platform maps to the WindowsMobile package -->
<PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">C0CCF48E-4BFB-4d84-827C-981A595E40C5</PROPERTY>
<!-- Crossbow Beta Pocket PC maps to the WindowsMobile Package -->
<PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">C0CCF48E-4BFB-4d84-827C-981A595E40C5</PROPERTY>
<!-- Crossbow Beta Smartphone maps to the WindowsMobile Package -->
<PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">C0CCF48E-4BFB-4d84-827C-981A595E40C5</PROPERTY>
</PROPERTYCONTAINER>
</FILE>
</FILECONTAINER>
</ADDON>
</ADDONCONTAINER>
</xsl:template> 
</xsl:stylesheet>
