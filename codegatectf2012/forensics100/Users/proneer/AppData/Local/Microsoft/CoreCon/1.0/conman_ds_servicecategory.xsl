<?xml version="1.0" standalone="no"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="/">
		<SERVICECATEGORYCONTAINER xmlns:dt="urn:schemas-microsoft-com:datatypes" Protected="false">
			<SERVICECATEGORY Name="Application Level Transport Service" ID="B333580E-3924-492e-98E5-DF57E787591B">
				<SERVICEINFOCONTAINER>
					<xsl:element name="SERVICEINFO">
						<xsl:attribute name="Name">
							<xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/TRANSPORT_TRANSPORTNAME_TCPCONNECT"/>
						</xsl:attribute>
						<xsl:attribute name="ID">D8E78E43-D8D6-4e57-8AD4-2164254C16D5</xsl:attribute>
						<xsl:attribute name="Protected">true</xsl:attribute>
						<PROPERTYCONTAINER>
							<PROPERTY ID="default" Protected="true">yes</PROPERTY>
							<PROPERTY ID="type" Protected="true">tcp_connect</PROPERTY>
							<PROPERTY ID="ip" Protected="true">0.0.0.1</PROPERTY>
							<PROPERTY ID="port" Protected="true">5655</PROPERTY>
							<PROPERTY ID="device:ip" Protected="true">0.0.0.1</PROPERTY>
							<PROPERTY ID="device:port" Protected="true">5655</PROPERTY>
							<PROPERTY ID="LocalAssemblyFile" Protected="true" _UseCcRelativePath="true">bin\tcpconnectionc.dll</PROPERTY>
							<PROPERTY ID="DebuggerTransport" Protected="true" _UseCcRelativePath="true">transports\desktop\cmtnpt_tcpconnect.dll</PROPERTY>
							<PROPERTY ID="LocalTransportFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\TcpConnectionA.dll</PROPERTY>
						</PROPERTYCONTAINER>
					</xsl:element>
					<xsl:element name="SERVICEINFO">
						<xsl:attribute name="Name">
							<xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/TRANSPORT_TRANSPORTNAME_DMA"/>
						</xsl:attribute>
						<xsl:attribute name="ID">26753017-B5BB-4b67-BEE3-862676DE23DC</xsl:attribute>
						<xsl:attribute name="Protected">true</xsl:attribute>
						<PROPERTYCONTAINER>
							<PROPERTY ID="default" Protected="true">yes</PROPERTY>
							<PROPERTY ID="type" Protected="true">tcp_connect</PROPERTY>
							<PROPERTY ID="ip" Protected="true">0.0.0.1</PROPERTY>
							<PROPERTY ID="port" Protected="true">5655</PROPERTY>
							<PROPERTY ID="device:ip" Protected="true">0.0.0.1</PROPERTY>
							<PROPERTY ID="device:port" Protected="true">5655</PROPERTY>
							<PROPERTY ID="LocalAssemblyFile" Protected="true" _UseCcRelativePath="true">bin\desktopdma.dll</PROPERTY>
							<PROPERTY ID="DebuggerTransport" Protected="true" _UseCcRelativePath="true">transports\desktop\cmtnpt_tcpconnect.dll</PROPERTY>
							<PROPERTY ID="LocalTransportFile" Protected="true" _UseCcRelativePath="true">target\wce400\%cpu%\devicedma.dll</PROPERTY>
						</PROPERTYCONTAINER>
					</xsl:element>
				</SERVICEINFOCONTAINER>
				<PROPERTYCONTAINER>
					<PROPERTY ID="_AddonFile" Name="_AddonFile">Microsoft.ServiceCategories.xsl</PROPERTY></PROPERTYCONTAINER>
			</SERVICECATEGORY>
			<SERVICECATEGORY Name="Application Level Bootstrap Service" ID="D7C86969-EB5F-41e2-96CC-290683622203">
				<SERVICEINFOCONTAINER>
					<xsl:element name="SERVICEINFO">
						<xsl:attribute name="Name">
							<xsl:value-of select="LANGUAGE/MICROSOFT_SERVICECATEGORIES_8_0/STARTUP_STARTUPNAME_ACTIVESYNC"/>
						</xsl:attribute>
						<xsl:attribute name="ID">6CFC41FD-50BA-43d2-9ACD-6A2A874D2853</xsl:attribute>
						<xsl:attribute name="Protected">true</xsl:attribute>
						<PROPERTYCONTAINER>
							<PROPERTY ID="default" Protected="true">yes</PROPERTY>
							<PROPERTY ID="type" Protected="true">activesync</PROPERTY>
							<PROPERTY ID="LocalAssemblyFile" Protected="true" _UseCcRelativePath="true">bin\ActiveSyncBootstrap.dll</PROPERTY>
						</PROPERTYCONTAINER>
					</xsl:element>
					<xsl:element name="SERVICEINFO">
						<xsl:attribute name="Name">
							<xsl:value-of select="LANGUAGE/MICROSOFT_VISUALSTUDIO_SERVICECATEGORIES_8_0/STARTUP_STARTUPNAME_DEVICEEMULATION"/>
						</xsl:attribute>
						<xsl:attribute name="ID">ECDA0E20-34EF-41CD-9574-A51C52B45037</xsl:attribute>
						<xsl:attribute name="Protected">true</xsl:attribute>
						<PROPERTYCONTAINER>
							<PROPERTY ID="default" Protected="true">yes</PROPERTY>
							<PROPERTY ID="type" Protected="true">emulator</PROPERTY>
							<PROPERTY ID="LocalAssemblyFile" Protected="true" _UseVSRelativePath="true">smartdevices\emulators\DeviceEmulatorBootstrap.dll</PROPERTY>
							<PROPERTY ID="_AddonFile" Name="_AddonFile">Microsoft.VisualStudio.ServiceCategories.xsl</PROPERTY></PROPERTYCONTAINER>
					</xsl:element>
				</SERVICEINFOCONTAINER>
				<PROPERTYCONTAINER>
					<PROPERTY ID="_AddonFile" Name="_AddonFile">Microsoft.ServiceCategories.xsl</PROPERTY></PROPERTYCONTAINER>
			</SERVICECATEGORY>
		</SERVICECATEGORYCONTAINER>
	</xsl:template>
</xsl:stylesheet>
