<?xml version="1.0" standalone="no"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">

<ADDONCONTAINER>

<ADDON Priority="100">

<PLATFORMCONTAINER>

<xsl:element name="PLATFORM">
	<xsl:attribute name="Name">
		<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/PLATFORM_PLATNAME_WINDOWS_CE"/>
	</xsl:attribute>
	<xsl:attribute name="ID">E2BECB1F-8C8C-41ba-B736-9BE7D946A398</xsl:attribute>
	<xsl:attribute name="Protected">true</xsl:attribute>

	<DEVICECONTAINER>

	<xsl:element name="DEVICE">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/PLATFORM_DEVICENAME_WINDOWS_CE"/>
		</xsl:attribute>
		<xsl:attribute name="ID">81551346-886F-43a2-B707-A91FAE1B33A7</xsl:attribute>
		<xsl:attribute name="Protected">true</xsl:attribute>

		<PROPERTYCONTAINER>
		<PROPERTY ID="OS_Version" Protected="false">4000</PROPERTY>
		<PROPERTY ID="OS" Protected="false">default</PROPERTY>
		<PROPERTY ID="LocalClientFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\ConManClient2.exe</PROPERTY>
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
				<xsl:attribute name="Protected">false</xsl:attribute>
				<PROPERTYCONTAINER>
				<PROPERTY ID="default" Protected="false">no</PROPERTY>
				<PROPERTY ID="type" Protected="false">tcp_connect</PROPERTY>
				<PROPERTY ID="ip" Protected="false">127.0.0.1</PROPERTY>
				<PROPERTY ID="port" Protected="false">5655</PROPERTY>
				<PROPERTY ID="authenticate" Protected="false">false</PROPERTY>
				<PROPERTY ID="useAutoAddress" Protected="false">yes</PROPERTY>
				<PROPERTY ID="useCustomPort" Protected="false">no</PROPERTY>
				<PROPERTY ID="LocalTransportFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\TcpConnectionA.dll</PROPERTY>
				<PROPERTY ID="RemoteTransportFile" Protected="true">%CSIDL_WINDOWS%\TcpConnectionA.dll</PROPERTY>
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
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">\</xsl:attribute>                   <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_ROOT"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PERSONAL%</xsl:attribute>      <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_PERSONAL"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PROGRAMS%</xsl:attribute>      <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_PROGRAMS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_PROGRAM_FILES%</xsl:attribute> <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_PROGRAM_FILES"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_APPDATA%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_APPDATA"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_COMMON_APPDATA%</xsl:attribute><xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_COMMON_APPDATA"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_WINDOWS%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_WINDOWS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_FONTS%</xsl:attribute>         <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_FONTS"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_STARTMENU%</xsl:attribute>     <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_STARTMENU"/></xsl:attribute></xsl:element>
                <xsl:element name="PROPERTY"><xsl:attribute name="ID">%CSIDL_STARTUP%</xsl:attribute>       <xsl:attribute name="Name"><xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/CSIDL_STARTUP"/></xsl:attribute></xsl:element>
			</PROPERTYCONTAINER>
		</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	</DEVICECONTAINER>

	<PROJECTCONTAINER>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/PLATFORM_PROJECTNAME_WINDOWSAPP"/>
		</xsl:attribute>
		<xsl:attribute name="ID">Windows Application</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="Directory">Windows Application</PROPERTY>
			<PROPERTY ID="SortIndex">10</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/PLATFORM_PROJECTNAME_CLASSLIBRARY"/>
		</xsl:attribute>
		<xsl:attribute name="ID">Class Library</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="Directory">Class Library</PROPERTY>
			<PROPERTY ID="SortIndex">20</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/PLATFORM_PROJECTNAME_WINDOWSCTRLLIB"/>
		</xsl:attribute>
		<xsl:attribute name="ID">Windows Control Library</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="Directory">Windows Control Library</PROPERTY>
			<PROPERTY ID="SortIndex">25</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/PLATFORM_PROJECTNAME_CONSOLEAPP"/>
		</xsl:attribute>
		<xsl:attribute name="ID">Console Application</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="Directory">Console Application</PROPERTY>
			<PROPERTY ID="SortIndex">40</PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	<xsl:element name="PROJECT">
		<xsl:attribute name="Name">
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/PLATFORM_PROJECTNAME_EMPTY"/>
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
			<xsl:value-of select="LANGUAGE/MICROSOFT_WINDOWSCE/FORMFACTOR_FACTORNAME_WEBPAD"/>
		</xsl:attribute>
		<xsl:attribute name="ID">WEBPAD</xsl:attribute>
		<xsl:attribute name="Protected">true</xsl:attribute>
		<PROPERTYCONTAINER>
			<PROPERTY ID="DPIX">96</PROPERTY>
			<PROPERTY ID="DPIY">96</PROPERTY>
			<PROPERTY ID="SHOWSKIN">false</PROPERTY>
			<PROPERTY ID="SupportRotation">false</PROPERTY>
			<PROPERTY ID="DisplayWidth">640</PROPERTY>
			<PROPERTY ID="DisplayHeight">480</PROPERTY>
			<PROPERTY ID="ColorDepth">16</PROPERTY>
			<PROPERTY ID="Skin"></PROPERTY>
			<PROPERTY ID="KeyMapping"></PROPERTY>
		</PROPERTYCONTAINER>
	</xsl:element>
	</FORMFACTORCONTAINER>

	<PROPERTYCONTAINER>
		<PROPERTY ID="OSVersion" Protected="false">5.00</PROPERTY>
		<PROPERTY ID="Profile" Protected="false">yes</PROPERTY>
		<PROPERTY ID="SupportedProfile" Protected="false">Generic Compact Profile</PROPERTY>
		<PROPERTY ID="COM+ReferenceDir" Protected="false" _UseVSRelativePath="true">smartdevices\sdk\compactframework\v2.0\WindowsCE</PROPERTY>
		<PROPERTY ID="DefaultPlatform" Protected="false">3C41C503-53EF-4c2a-8DD4-A8217CAD115E</PROPERTY>
		<PROPERTY ID="DefaultDevice" Protected="false">E282E6BE-C7C3-4ece-916A-88FB1CF8AF3C</PROPERTY>
		<PROPERTY ID="WizardSortOrder" Protected="false">30</PROPERTY>
		<PROPERTY ID="UserListed" Protected="false">yes</PROPERTY>
		<PROPERTY ID="ShortName" Protected="false">WCE4</PROPERTY>
		<PROPERTY ID="Directory" Protected="false">Windows CE</PROPERTY>
		<PROPERTY ID="DefaultFormFactor" Protected="false">WEBPAD</PROPERTY>
        <PROPERTY ID="PlatformFamily" Protected="false">WindowsCE</PROPERTY>
        <PROPERTY ID="ShowInNewProjectDialog" Protected="false">true</PROPERTY>
        <PROPERTY ID="SupportedLanguages">
            <PROPERTYCONTAINER>
                <PROPERTY ID="CSharp" Protected="false">CSharp</PROPERTY>
                <PROPERTY ID="VisualBasic" Protected="false">VisualBasic</PROPERTY>
                <PROPERTY ID="C++" Protected="false">C++</PROPERTY>
            </PROPERTYCONTAINER>
        </PROPERTY>
        <PROPERTY ID="SupportedNETCFVersions">
            <PROPERTYCONTAINER>
                <PROPERTY ID=".NETCF 2.0" Protected="false">2.0</PROPERTY>
                <PROPERTY ID=".NETCF 3.5" Protected="false">3.5</PROPERTY>
            </PROPERTYCONTAINER>
        </PROPERTY>

    </PROPERTYCONTAINER>
</xsl:element>

</PLATFORMCONTAINER>

</ADDON>

</ADDONCONTAINER>

</xsl:template>
</xsl:stylesheet>
