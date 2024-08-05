<?xml version="1.0" standalone="no"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">

<ADDONCONTAINER>

<ADDON Priority="100">

<PLATFORMCONTAINER>

<xsl:element name="PLATFORM">
	<xsl:attribute name="Name">
		<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/PLATFORM_PLATNAME_SMARTPHONE"/>
	</xsl:attribute>
	<xsl:attribute name="ID">4DE813A2-67E0-4a00-945C-3188240A8243</xsl:attribute>
	<xsl:attribute name="Protected">true</xsl:attribute>

	<DEVICECONTAINER>

	<xsl:element name="DEVICE">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/PLATFORM_DEVICENAME_SMARTPHONE_DEVICEEMULATOR"/>
		</xsl:attribute>
		<xsl:attribute name="ID">DD63BCFB-BCB3-407c-9CDC-219A0240CBA0</xsl:attribute>
		<xsl:attribute name="Protected">true</xsl:attribute>

		<PROPERTYCONTAINER>
                <PROPERTY ID="DeviceFamily" Protected="true">Smartphone</PROPERTY>
		<PROPERTY ID="OS_Version" Protected="false">4000</PROPERTY>
		<PROPERTY ID="OS" Protected="false">default</PROPERTY>
		<PROPERTY ID="Emulator" Protected="true">true</PROPERTY>
                <PROPERTY ID="CpuName">ARMV4</PROPERTY>
		<PROPERTY ID="Platform" Protected="false">default</PROPERTY>
		<PROPERTY ID="LocalClientFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="RemoteClientFile" Protected="true">\Windows\CoreCon%CcVersion%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="LocalShutdownFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\ClientShutdown.exe</PROPERTY>
		<PROPERTY ID="RemoteShutdownFile" Protected="true">\Windows\CoreCon%CcVersion%\ClientShutdown.exe</PROPERTY>

		<PROPERTY ID="RemoteCcClientFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="RemoteCcShutdownFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\ClientShutdown.exe</PROPERTY>
		<PROPERTY ID="RemoteCcTransportLoaderFile" Protected="true">%CSIDL_WINDOWS%\eDbgTL.dll</PROPERTY>
		<PROPERTY ID="RemoteCcCMAcceptFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\CMAccept.exe</PROPERTY>

		<!-- The current transport is the emulator transport -->
		<PROPERTY ID="B333580E-3924-492e-98E5-DF57E787591B" Protected="false">26753017-B5BB-4b67-BEE3-862676DE23DC</PROPERTY>
		<!-- The current boostrap is the emulator bootstrap -->
		<PROPERTY ID="D7C86969-EB5F-41e2-96CC-290683622203" Protected="true">ECDA0E20-34EF-41CD-9574-A51C52B45037</PROPERTY>

		<!-- Transport service property overrides -->
		<PROPERTY ID="B333580E-3924-492e-98E5-DF57E787591B_ALL">
			<PROPERTYCONTAINER>
			<!-- Emulation Transport -->
			<xsl:element name="PROPERTY">
				<xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/TRANSPORT_TRANSPORTNAME_TCPCONNECT"/></xsl:attribute>
				<xsl:attribute name="ID">D8E78E43-D8D6-4e57-8AD4-2164254C16D5</xsl:attribute>
				<xsl:attribute name="Protected">false</xsl:attribute>
				<PROPERTYCONTAINER>
				<PROPERTY ID="default" Protected="false">no</PROPERTY>
				<PROPERTY ID="type" Protected="false">emulator</PROPERTY>
				<PROPERTY ID="LocalTransportFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\tcpconnectiona.dll</PROPERTY>
				<PROPERTY ID="RemoteTransportFile" Protected="true">%CSIDL_WINDOWS%\tcpconnectiona.dll</PROPERTY>
				<PROPERTY ID="ip" Protected="false">127.0.0.1</PROPERTY>
				<PROPERTY ID="port" Protected="false">5654</PROPERTY>
				<PROPERTY ID="useCustomPort" Protected="false">no</PROPERTY>
				<PROPERTY ID="authenticate" Protected="false">false</PROPERTY>
				</PROPERTYCONTAINER>
			</xsl:element>
				<!-- DMA Emulator Transport -->
				<xsl:element name="PROPERTY">
					<xsl:attribute name="Name">
						<xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/TRANSPORT_TRANSPORTNAME_DMA"/>
					</xsl:attribute>
					<xsl:attribute name="ID">26753017-B5BB-4b67-BEE3-862676DE23DC</xsl:attribute>
					<xsl:attribute name="Protected">false</xsl:attribute>
					<PROPERTYCONTAINER>
						<PROPERTY ID="default" Protected="false">no</PROPERTY>
						<PROPERTY ID="type" Protected="false">emulator</PROPERTY>
						<PROPERTY ID="LocalTransportFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\devicedma.dll</PROPERTY>
						<PROPERTY ID="RemoteTransportFile" Protected="true">%CSIDL_WINDOWS%\devicedma.dll</PROPERTY>
						<PROPERTY ID="ip" Protected="false">127.0.0.1</PROPERTY>
						<PROPERTY ID="port" Protected="false">5654</PROPERTY>
						<PROPERTY ID="authenticate" Protected="false">false</PROPERTY>
					</PROPERTYCONTAINER>
				</xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>

		<!-- Bootstrap service property overrides -->
		<PROPERTY ID="D7C86969-EB5F-41e2-96CC-290683622203_ALL">
			<PROPERTYCONTAINER>
			<!-- Emulation Bootstrap -->
			<xsl:element name="PROPERTY">
				<xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_VISUALSTUDIO_SERVICECATEGORIES_8_0/STARTUP_STARTUPNAME_DEVICEEMULATION"/></xsl:attribute>
				<xsl:attribute name="ID">ECDA0E20-34EF-41CD-9574-A51C52B45037</xsl:attribute>
				<xsl:attribute name="Protected">false</xsl:attribute>
				<PROPERTYCONTAINER>
				<PROPERTY ID="default" Protected="false">no</PROPERTY>
				<PROPERTY ID="type" Protected="false">emulator</PROPERTY>
				<PROPERTY ID="VMID" Protected="false"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/VMID_SMARTPHONE_DEVICEEMULATOR"/></PROPERTY>
				<PROPERTY ID="OSBinImage" Protected="false" _UseVSRelativePath="true">smartdevices\emulators\Images\Smartphone\2003\<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/DEFAULT_EMULATOR_LCID"/>\<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/SMARTPHONE_DEVICEEMULATOR_BINNAME"/></PROPERTY>
				<PROPERTY ID="ScreenWidth" Protected="false">176</PROPERTY>
				<PROPERTY ID="ScreenHeight" Protected="false">220</PROPERTY>
				<PROPERTY ID="ColorDepth" Protected="false">16</PROPERTY>
				<PROPERTY ID="EthernetEnabled" Protected="false">no</PROPERTY>
				<PROPERTY ID="RAMSize" Protected="false">64</PROPERTY>
				<PROPERTY ID="HostKey" Protected="false">165</PROPERTY>
                                <PROPERTY ID="UseDefaultSaveState" Protected="false">true</PROPERTY>
				<PROPERTY ID="SerialPort0" Name="SerialPort0"></PROPERTY>
				<PROPERTY ID="SerialPort1" Protected="false"></PROPERTY>
				<PROPERTY ID="SerialPort2" Protected="false"></PROPERTY>
				<PROPERTY ID="ParallelPort" Protected="false"></PROPERTY>
				<PROPERTY ID="AlwaysOnTop" Name="AlwaysOnTop">no</PROPERTY>
				<PROPERTY ID="CreateConsole" Name="CreateConsole">no</PROPERTY>
				<PROPERTY ID="CS8900Adapter" Name="CS8900Adapter">000000000000</PROPERTY>
				<PROPERTY ID="CS8900EthernetEnabled" Name="CS8900EthernetEnabled">no</PROPERTY>
				<PROPERTY ID="EnableToolTips" Name="EnableToolTips">no</PROPERTY>
				<PROPERTY ID="FlashFile" Name="FlashFile"></PROPERTY>
				<PROPERTY ID="HostOnlyEthernetEnabled" Name="HostOnlyEthernetEnabled">no</PROPERTY>
				<PROPERTY ID="ImageAddress" Name="ImageAddress"></PROPERTY>
				<PROPERTY ID="NE2000Adapter" Name="NE2000Adapter">000000000000</PROPERTY>
				<PROPERTY ID="Orientation" Name="Orientation">0</PROPERTY>
				<PROPERTY ID="ShowSkin" Name="ShowSkin">yes</PROPERTY>
				<PROPERTY ID="SpecifyAddress" Name="SpecifyAddress">no</PROPERTY>
				<PROPERTY ID="Zoom2x" Name="Zoom2x">no</PROPERTY>
				<PROPERTY ID="Skin" Protected="false" _UseVSRelativePath="true">smartdevices\Skins\Smartphone_2003\Smartphone_2003\<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/DEFAULT_EMULATOR_LCID"/>\Smartphone_2003_Skin.xml</PROPERTY>
				<PROPERTY ID="SkinEngine" Protected="false" _UseVSRelativePath="true">smartdevices\emulators\skin.dll</PROPERTY>
				</PROPERTYCONTAINER>
			</xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>

        <PROPERTY ID="OutputLocation">%CSIDL_PROGRAM_FILES%</PROPERTY>
		<PROPERTY ID="OutputLocation_ALL">
			<PROPERTYCONTAINER>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">\</xsl:attribute>                     <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_ROOT"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PERSONAL%</xsl:attribute>      <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PERSONAL"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PROGRAMS%</xsl:attribute>      <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PROGRAMS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PROGRAM_FILES%</xsl:attribute> <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PROGRAM_FILES"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_APPDATA%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_APPDATA"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_COMMON_APPDATA%</xsl:attribute><xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_COMMON_APPDATA"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_WINDOWS%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_WINDOWS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_FONTS%</xsl:attribute>         <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_FONTS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_STARTMENU%</xsl:attribute>     <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_STARTMENU"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_STARTUP%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_STARTUP"/></xsl:attribute></xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>

	<xsl:element name="DEVICE">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/PLATFORM_DEVICENAME_SMARTPHONE_VGA_DEVICEEMULATOR"/>
		</xsl:attribute>
		<xsl:attribute name="ID">C8E03CB2-D57F-431c-8C10-06DA8135B177</xsl:attribute>
		<xsl:attribute name="Protected">true</xsl:attribute>

		<PROPERTYCONTAINER>
                <PROPERTY ID="DeviceFamily" Protected="true">Smartphone</PROPERTY>
		<PROPERTY ID="OS_Version" Protected="false">4000</PROPERTY>
		<PROPERTY ID="OS" Protected="false">default</PROPERTY>
		<PROPERTY ID="Emulator" Protected="true">true</PROPERTY>
                <PROPERTY ID="CpuName">ARMV4</PROPERTY>
		<PROPERTY ID="Platform" Protected="false">default</PROPERTY>
		<PROPERTY ID="LocalClientFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="RemoteClientFile" Protected="true">\Windows\CoreCon%CcVersion%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="LocalShutdownFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\ClientShutdown.exe</PROPERTY>
		<PROPERTY ID="RemoteShutdownFile" Protected="true">\Windows\CoreCon%CcVersion%\ClientShutdown.exe</PROPERTY>

		<PROPERTY ID="RemoteCcClientFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="RemoteCcShutdownFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\ClientShutdown.exe</PROPERTY>
		<PROPERTY ID="RemoteCcTransportLoaderFile" Protected="true">%CSIDL_WINDOWS%\eDbgTL.dll</PROPERTY>
		<PROPERTY ID="RemoteCcCMAcceptFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\CMAccept.exe</PROPERTY>

		<!-- The current transport is the emulator transport -->
		<PROPERTY ID="B333580E-3924-492e-98E5-DF57E787591B" Protected="false">26753017-B5BB-4b67-BEE3-862676DE23DC</PROPERTY>
		<!-- The current boostrap is the emulator bootstrap -->
		<PROPERTY ID="D7C86969-EB5F-41e2-96CC-290683622203" Protected="true">ECDA0E20-34EF-41CD-9574-A51C52B45037</PROPERTY>

		<!-- Transport service property overrides -->
		<PROPERTY ID="B333580E-3924-492e-98E5-DF57E787591B_ALL">
			<PROPERTYCONTAINER>
			<!-- Emulation Transport -->
			<xsl:element name="PROPERTY">
				<xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/TRANSPORT_TRANSPORTNAME_TCPCONNECT"/></xsl:attribute>
				<xsl:attribute name="ID">D8E78E43-D8D6-4e57-8AD4-2164254C16D5</xsl:attribute>
				<xsl:attribute name="Protected">false</xsl:attribute>
				<PROPERTYCONTAINER>
				<PROPERTY ID="default" Protected="false">no</PROPERTY>
				<PROPERTY ID="type" Protected="false">emulator</PROPERTY>
				<PROPERTY ID="LocalTransportFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\tcpconnectiona.dll</PROPERTY>
				<PROPERTY ID="RemoteTransportFile" Protected="true">%CSIDL_WINDOWS%\tcpconnectiona.dll</PROPERTY>
				<PROPERTY ID="ip" Protected="false">127.0.0.1</PROPERTY>
				<PROPERTY ID="port" Protected="false">5654</PROPERTY>
				<PROPERTY ID="useCustomPort" Protected="false">no</PROPERTY>
				<PROPERTY ID="authenticate" Protected="false">false</PROPERTY>
				</PROPERTYCONTAINER>
			</xsl:element>
				<!-- DMA Emulator Transport -->
				<xsl:element name="PROPERTY">
					<xsl:attribute name="Name">
						<xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/TRANSPORT_TRANSPORTNAME_DMA"/>
					</xsl:attribute>
					<xsl:attribute name="ID">26753017-B5BB-4b67-BEE3-862676DE23DC</xsl:attribute>
					<xsl:attribute name="Protected">false</xsl:attribute>
					<PROPERTYCONTAINER>
						<PROPERTY ID="default" Protected="false">no</PROPERTY>
						<PROPERTY ID="type" Protected="false">emulator</PROPERTY>
						<PROPERTY ID="LocalTransportFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\devicedma.dll</PROPERTY>
						<PROPERTY ID="RemoteTransportFile" Protected="true">%CSIDL_WINDOWS%\devicedma.dll</PROPERTY>
						<PROPERTY ID="ip" Protected="false">127.0.0.1</PROPERTY>
						<PROPERTY ID="port" Protected="false">5654</PROPERTY>
						<PROPERTY ID="authenticate" Protected="false">false</PROPERTY>
					</PROPERTYCONTAINER>
				</xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>

		<!-- Bootstrap service property overrides -->
		<PROPERTY ID="D7C86969-EB5F-41e2-96CC-290683622203_ALL">
			<PROPERTYCONTAINER>
			<!-- Emulation Bootstrap -->
			<xsl:element name="PROPERTY">
				<xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_VISUALSTUDIO_SERVICECATEGORIES_8_0/STARTUP_STARTUPNAME_DEVICEEMULATION"/></xsl:attribute>
				<xsl:attribute name="ID">ECDA0E20-34EF-41CD-9574-A51C52B45037</xsl:attribute>
				<xsl:attribute name="Protected">false</xsl:attribute>
				<PROPERTYCONTAINER>
				<PROPERTY ID="default" Protected="false">no</PROPERTY>
				<PROPERTY ID="type" Protected="false">emulator</PROPERTY>
				<PROPERTY ID="VMID" Protected="false"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/VMID_SMARTPHONE_VGA_DEVICEEMULATOR"/></PROPERTY>
				<PROPERTY ID="OSBinImage" Protected="false" _UseVSRelativePath="true">smartdevices\emulators\Images\Smartphone\2003\QVGA\<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/DEFAULT_EMULATOR_LCID"/>\<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/SMARTPHONE_DEVICEEMULATOR_QVGA_BINNAME"/></PROPERTY>
				<PROPERTY ID="ScreenWidth" Protected="false">352</PROPERTY>
				<PROPERTY ID="ScreenHeight" Protected="false">440</PROPERTY>
				<PROPERTY ID="ColorDepth" Protected="false">16</PROPERTY>
				<PROPERTY ID="EthernetEnabled" Protected="false">no</PROPERTY>
				<PROPERTY ID="RAMSize" Protected="false">64</PROPERTY>
				<PROPERTY ID="HostKey" Protected="false">165</PROPERTY>
                                <PROPERTY ID="UseDefaultSaveState" Protected="false">true</PROPERTY>
				<PROPERTY ID="SerialPort0" Name="SerialPort0"></PROPERTY>
				<PROPERTY ID="SerialPort1" Protected="false"></PROPERTY>
				<PROPERTY ID="SerialPort2" Protected="false"></PROPERTY>
				<PROPERTY ID="ParallelPort" Protected="false"></PROPERTY>
				<PROPERTY ID="AlwaysOnTop" Name="AlwaysOnTop">no</PROPERTY>
				<PROPERTY ID="CreateConsole" Name="CreateConsole">no</PROPERTY>
				<PROPERTY ID="CS8900Adapter" Name="CS8900Adapter">000000000000</PROPERTY>
				<PROPERTY ID="CS8900EthernetEnabled" Name="CS8900EthernetEnabled">no</PROPERTY>
				<PROPERTY ID="EnableToolTips" Name="EnableToolTips">no</PROPERTY>
				<PROPERTY ID="FlashFile" Name="FlashFile"></PROPERTY>
				<PROPERTY ID="HostOnlyEthernetEnabled" Name="HostOnlyEthernetEnabled">no</PROPERTY>
				<PROPERTY ID="ImageAddress" Name="ImageAddress"></PROPERTY>
				<PROPERTY ID="NE2000Adapter" Name="NE2000Adapter">000000000000</PROPERTY>
				<PROPERTY ID="Orientation" Name="Orientation">0</PROPERTY>
				<PROPERTY ID="ShowSkin" Name="ShowSkin">yes</PROPERTY>
				<PROPERTY ID="SpecifyAddress" Name="SpecifyAddress">no</PROPERTY>
				<PROPERTY ID="Zoom2x" Name="Zoom2x">no</PROPERTY>
				<PROPERTY ID="Skin" Protected="false" _UseVSRelativePath="true">smartdevices\Skins\Smartphone_2003\Smartphone_2003_QVGA\<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/DEFAULT_EMULATOR_LCID"/>\Smartphone_2003_QVGA_Skin.xml</PROPERTY>
				<PROPERTY ID="SkinEngine" Protected="false" _UseVSRelativePath="true">smartdevices\emulators\skin.dll</PROPERTY>
				</PROPERTYCONTAINER>
			</xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>

        <PROPERTY ID="OutputLocation">%CSIDL_PROGRAM_FILES%</PROPERTY>
		<PROPERTY ID="OutputLocation_ALL">
			<PROPERTYCONTAINER>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">\</xsl:attribute>                     <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_ROOT"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PERSONAL%</xsl:attribute>      <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PERSONAL"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PROGRAMS%</xsl:attribute>      <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PROGRAMS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PROGRAM_FILES%</xsl:attribute> <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PROGRAM_FILES"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_APPDATA%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_APPDATA"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_COMMON_APPDATA%</xsl:attribute><xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_COMMON_APPDATA"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_WINDOWS%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_WINDOWS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_FONTS%</xsl:attribute>         <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_FONTS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_STARTMENU%</xsl:attribute>     <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_STARTMENU"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_STARTUP%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_STARTUP"/></xsl:attribute></xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>


	<xsl:element name="DEVICE">
		<xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/PLATFORM_DEVICENAME_SMARTPHONE"/></xsl:attribute>
		<xsl:attribute name="ID">1D18503C-8FA5-4715-84A3-9C14FDEDC54E</xsl:attribute>
		<xsl:attribute name="Protected">true</xsl:attribute>

		<PROPERTYCONTAINER>
                <PROPERTY ID="DeviceFamily" Protected="true">Smartphone</PROPERTY>
		<PROPERTY ID="OS_Version" Protected="false">4000</PROPERTY>
		<PROPERTY ID="OS" Protected="false">default</PROPERTY>
		<PROPERTY ID="Emulator" Protected="true">false</PROPERTY>
                <PROPERTY ID="CpuName">ARMV4</PROPERTY>
		<PROPERTY ID="LocalClientFile" Protected="true" _UseCcRelativePath="true">Target\wce400\%cpu%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="RemoteClientFile" Protected="true">\Windows\CoreCon%CcVersion%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="LocalShutdownFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\ClientShutdown.exe</PROPERTY>
		<PROPERTY ID="RemoteShutdownFile" Protected="true">\Windows\CoreCon%CcVersion%\ClientShutdown.exe</PROPERTY>

		<PROPERTY ID="RemoteCcClientFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\ConManClient2.exe</PROPERTY>
		<PROPERTY ID="RemoteCcShutdownFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\ClientShutdown.exe</PROPERTY>
		<PROPERTY ID="RemoteCcTransportLoaderFile" Protected="true">%CSIDL_WINDOWS%\eDbgTL.dll</PROPERTY>
		<PROPERTY ID="RemoteCcCMAcceptFile" Protected="true">%CSIDL_WINDOWS%\CoreCon%CcVersion%\CMAccept.exe</PROPERTY>

		<!-- The current transport is the TCP connect transport -->
		<PROPERTY ID="B333580E-3924-492e-98E5-DF57E787591B" Protected="false">D8E78E43-D8D6-4e57-8AD4-2164254C16D5</PROPERTY>
		<!-- The current boostrap is the ActiveSync bootstrap -->
		<PROPERTY ID="D7C86969-EB5F-41e2-96CC-290683622203" Protected="false">6CFC41FD-50BA-43d2-9ACD-6A2A874D2853</PROPERTY>

		<!-- Transport Service property overrides -->
		<PROPERTY ID="B333580E-3924-492e-98E5-DF57E787591B_ALL">
			<PROPERTYCONTAINER>

			<!-- TCP Connect Transport -->
			<xsl:element name="PROPERTY">
				<xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/TRANSPORT_TRANSPORTNAME_TCPCONNECT"/></xsl:attribute>
				<xsl:attribute name="ID">D8E78E43-D8D6-4e57-8AD4-2164254C16D5</xsl:attribute>
				<xsl:attribute name="Protected">true</xsl:attribute>
				<PROPERTYCONTAINER>
				<PROPERTY ID="default" Protected="false">no</PROPERTY>
				<PROPERTY ID="type" Protected="false">tcp_connect</PROPERTY>
				<PROPERTY ID="ip" Protected="false">127.0.0.1</PROPERTY>
				<PROPERTY ID="port" Protected="false">5655</PROPERTY>
				<PROPERTY ID="authenticate" Protected="false">false</PROPERTY>
				<PROPERTY ID="useAutoAddress" Protected="false">yes</PROPERTY>
				<PROPERTY ID="useCustomPort" Protected="false">no</PROPERTY>
				<PROPERTY ID="LocalTransportFile" Protected="true" _UseCcRelativePath="true">Target\wce400\%cpu%\tcpconnectiona.dll</PROPERTY>
				<PROPERTY ID="RemoteTransportFile" Protected="true">%CSIDL_WINDOWS%\tcpconnectiona.dll</PROPERTY>
				</PROPERTYCONTAINER>
			</xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>

		<!-- Bootstrap service property overrides -->
		<PROPERTY ID="D7C86969-EB5F-41e2-96CC-290683622203_ALL">
			<PROPERTYCONTAINER>

			<!-- ActiveSync Bootstrap -->
			<xsl:element name="PROPERTY">
				<xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/STARTUP_STARTUPNAME_ACTIVESYNC"/></xsl:attribute>
				<xsl:attribute name="ID">6CFC41FD-50BA-43d2-9ACD-6A2A874D2853</xsl:attribute>
				<xsl:attribute name="Protected">false</xsl:attribute>
				<PROPERTYCONTAINER>
				<PROPERTY ID="default" Protected="false">no</PROPERTY>
				<PROPERTY ID="type" Protected="false">activesync</PROPERTY>
				</PROPERTYCONTAINER>
			</xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>

        <PROPERTY ID="OutputLocation">%CSIDL_PROGRAM_FILES%</PROPERTY>
		<PROPERTY ID="OutputLocation_ALL">
			<PROPERTYCONTAINER>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">\</xsl:attribute>                     <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_ROOT"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PERSONAL%</xsl:attribute>      <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PERSONAL"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PROGRAMS%</xsl:attribute>      <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PROGRAMS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PROGRAM_FILES%</xsl:attribute> <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_PROGRAM_FILES"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_APPDATA%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_APPDATA"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_COMMON_APPDATA%</xsl:attribute><xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_COMMON_APPDATA"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_WINDOWS%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_WINDOWS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_FONTS%</xsl:attribute>         <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_FONTS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_STARTMENU%</xsl:attribute>     <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_STARTMENU"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_STARTUP%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/CSIDL_STARTUP"/></xsl:attribute></xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>

	</DEVICECONTAINER>

	<PROJECTCONTAINER>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/PLATFORM_PROJECTNAME_WINDOWSAPP"/>
		</xsl:attribute>
		<xsl:attribute name="ID">Windows Application</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="Directory">Windows Application</PROPERTY>
			<PROPERTY ID="SortIndex">10</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/PLATFORM_PROJECTNAME_CLASSLIBRARY"/>
		</xsl:attribute>
		<xsl:attribute name="ID">Class Library</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="Directory">Class Library</PROPERTY>
			<PROPERTY ID="SortIndex">20</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/PLATFORM_PROJECTNAME_WINDOWSCTRLLIB"/>
		</xsl:attribute>
		<xsl:attribute name="ID">Windows Control Library</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="Directory">Windows Control Library</PROPERTY>
			<PROPERTY ID="SortIndex">25</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/PLATFORM_PROJECTNAME_EMPTY"/>
		</xsl:attribute>
		<xsl:attribute name="ID">Empty Project</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="Directory">Empty Project</PROPERTY>
			<PROPERTY ID="SortIndex">50</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	</PROJECTCONTAINER>

	<FORMFACTORCONTAINER>
	<xsl:element name="FORMFACTOR">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/FORMFACTOR_FACTORNAME_SMARTPHONE_2003"/>
		</xsl:attribute>
		<xsl:attribute name="ID">SMARTPHONE_2003</xsl:attribute>
		<xsl:attribute name="Protected">true</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="DPIX">96</PROPERTY>
			<PROPERTY ID="DPIY">96</PROPERTY>
			<PROPERTY ID="SHOWSKIN">true</PROPERTY>
			<PROPERTY ID="SupportRotation">false</PROPERTY>
			<PROPERTY ID="DisplayWidth">176</PROPERTY>
			<PROPERTY ID="DisplayHeight">220</PROPERTY>
			<PROPERTY ID="ColorDepth">16</PROPERTY>
			<PROPERTY ID="Skin" Protected="false" _UseVSRelativePath="true">smartdevices\Skins\Smartphone_2003\Smartphone_2003\<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/DEFAULT_EMULATOR_LCID"/>\Smartphone_2003_Skin.xml</PROPERTY>
			<PROPERTY ID="KeyMapping"></PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>

	<xsl:element name="FORMFACTOR">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/FORMFACTOR_FACTORNAME_SMARTPHONE_2003_QVGA"/>
		</xsl:attribute>
		<xsl:attribute name="ID">SMARTPHONE_2003_QVGA</xsl:attribute>
		<xsl:attribute name="Protected">true</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="DPIX">131</PROPERTY>
			<PROPERTY ID="DPIY">131</PROPERTY>
			<PROPERTY ID="SHOWSKIN">true</PROPERTY>
			<PROPERTY ID="SupportRotation">false</PROPERTY>
			<PROPERTY ID="DisplayWidth">240</PROPERTY>
			<PROPERTY ID="DisplayHeight">320</PROPERTY>
			<PROPERTY ID="ColorDepth">16</PROPERTY>
			<PROPERTY ID="Skin" Protected="false" _UseVSRelativePath="true">smartdevices\Skins\Smartphone_2003\Smartphone_2003_QVGA\<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSMOBILE_SMARTPHONE_1_0/DEFAULT_EMULATOR_LCID"/>\Smartphone_2003_QVGA_Skin.xml</PROPERTY>
			<PROPERTY ID="KeyMapping"></PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	</FORMFACTORCONTAINER>

	<PROPERTYCONTAINER>
		<PROPERTY ID="OSVersion" Protected="false">4.20</PROPERTY>
		<PROPERTY ID="Profile" Protected="false">yes</PROPERTY>
		<PROPERTY ID="SupportedProfile" Protected="false">Generic Compact Profile</PROPERTY>
		<PROPERTY ID="COM+ReferenceDir" Protected="false" _UseVSRelativePath="true">smartdevices\sdk\compactframework\v1.0\WindowsCE</PROPERTY>
		<PROPERTY ID="DefaultPlatform" Protected="false">4DE813A2-67E0-4a00-945C-3188240A8243</PROPERTY>
		<PROPERTY ID="DefaultDevice" Protected="false">DD63BCFB-BCB3-407c-9CDC-219A0240CBA0</PROPERTY>
		<PROPERTY ID="RemoteClientFile" Protected="false">\ConManClient.exe</PROPERTY>
		<PROPERTY ID="WizardSortOrder" Protected="false">20</PROPERTY>
		<PROPERTY ID="UserListed" Protected="false">yes</PROPERTY>
		<PROPERTY ID="ShortName" Protected="false">SMP</PROPERTY>
		<PROPERTY ID="Directory" Protected="false">Smartphone</PROPERTY>
		<PROPERTY ID="DefaultFormFactor" Protected="false">SMARTPHONE_2003</PROPERTY>
    <PROPERTY ID="PlatformFamily" Protected="false">Smartphone</PROPERTY>
	</PROPERTYCONTAINER>
</xsl:element>

</PLATFORMCONTAINER>

<PACKAGECONTAINER xmlns:dt="urn:schemas-microsoft-com:datatypes" Protected="false" Version="$majorHigh$.$majorLow$.$minorHigh$.$minorLow$">

<!-- cmtnpt_TcpAcceptNA package -->
<!-- Used to debug SP2003 .NetCF V1.0 images -->

<PACKAGE NAME="cmtnpt_TcpAcceptNA.dll" ID="cmtnpt_TcpAcceptNA.dll">

<PROPERTYCONTAINER>
</PROPERTYCONTAINER>

<PACKAGETYPECONTAINER>

<PACKAGETYPE Name="ARMV4" ID="ARMV4" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">SmartDevices\Debugger\Target\wce400\armv4\</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="cmtnpt_TcpAcceptNA.dll"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">SmartDevices\Debugger\Target\wce400\armv4i\</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="cmtnpt_TcpAcceptNA.dll"/>
  </FILECONTAINER>
</PACKAGETYPE>

</PACKAGETYPECONTAINER>

</PACKAGE>

</PACKAGECONTAINER>

</ADDON>

</ADDONCONTAINER>

</xsl:template>
</xsl:stylesheet>
