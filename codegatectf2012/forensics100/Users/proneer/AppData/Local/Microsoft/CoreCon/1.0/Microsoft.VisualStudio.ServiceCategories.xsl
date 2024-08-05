<?xml version="1.0" standalone="no"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">

<ADDONCONTAINER>

<ADDON Priority="100">

<SERVICECATEGORYCONTAINER>


<SERVICECATEGORY Name="Application Level Bootstrap Service" ID="D7C86969-EB5F-41e2-96CC-290683622203">
	<SERVICEINFOCONTAINER>
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
		</PROPERTYCONTAINER>
	</xsl:element>
	</SERVICEINFOCONTAINER>

	<PROPERTYCONTAINER>
	</PROPERTYCONTAINER>
</SERVICECATEGORY>

</SERVICECATEGORYCONTAINER>

</ADDON>

</ADDONCONTAINER>

</xsl:template>
</xsl:stylesheet>
