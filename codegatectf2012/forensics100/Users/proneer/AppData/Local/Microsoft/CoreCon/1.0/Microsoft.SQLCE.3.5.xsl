<?xml version="1.0" standalone="no"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/">
		<ADDONCONTAINER>
			<ADDON>
				<!-- ********************************************************************************************************* -->
				<!--                                                                                                           -->
				<!-- This section associates individual assemblies with the packages that have to get installed                -->
				<!--                                                                                                           -->
				<!-- ********************************************************************************************************* -->
				<FILECONTAINER>

					<!-- SSCE v3.5: Used if the developer adds SqlServerCe to the project -->
					<FILE ID="System.Data.SqlServerCe, Version=3.5.0.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3">
						<PROPERTYCONTAINER>
							<!-- Pocket PC (4.x) platform maps to the Pocket PC (4.x) package -->
							<PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">A51B2C18-0718-4729-B80D-D98EA4B63758</PROPERTY>
							<!-- Pocket PC (5.x) platform maps to the Pocket PC (5.x) package - Magneto & Crossbow -->
							<PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">647BFB62-BFF0-40EB-849C-115F042B19C5</PROPERTY>
							<PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">647BFB62-BFF0-40EB-849C-115F042B19C5</PROPERTY>
							<!-- Smartphone (5.x) platform maps to the Smartphone (5.x) package - Magneto & Crossbow -->
							<PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">8C8F2D09-DF3E-4F88-A967-7C012B25E0AC</PROPERTY>
							<PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">8C8F2D09-DF3E-4F88-A967-7C012B25E0AC</PROPERTY>
							<!-- Windows CE (5.x) platform maps to the Windows CE (5.x) package -->
							<PROPERTY ID="E2BECB1F-8C8C-41BA-B736-9BE7D946A398">515B5EC3-0DDB-4C3F-8589-82EC5052297A</PROPERTY>
						</PROPERTYCONTAINER>
					</FILE>

					<!-- SQL Client 2.0: Used if the developer adds SqlClient to the project -->
					<FILE ID="System.Data.SqlClient, Version=3.0.3600.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3">
						<PROPERTYCONTAINER>
							<!-- Pocket PC (4.0) platform maps to the Pocket PC (4.x) package -->
							<PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">64C8FE35-C04C-4BCA-9B48-1D67112787A8</PROPERTY>
							<!-- Pocket PC (5.0) platform maps to the Pocket PC (5.x) package - Magneto & Crossbow -->
							<PROPERTY ID="4118C335-430C-497f-BE48-11C3316B135E">2BD213E1-4DF6-41AA-81DD-E8C5C9CAF16D</PROPERTY>
							<PROPERTY ID="B2C48BD2-963D-4549-9169-1FA021DCE484">2BD213E1-4DF6-41AA-81DD-E8C5C9CAF16D</PROPERTY>
							<!-- Smartphone (5.0) platform maps to the Smartphone (5.x) package - Magneto & Crossbow -->
							<PROPERTY ID="BD0CC567-F6FD-4ca3-99D2-063EFDFC0A39">335E564D-9E41-4125-BBFB-ACB20152BA8F</PROPERTY>
							<PROPERTY ID="F27DA329-3269-4191-98E0-C87D3D7F1DB9">335E564D-9E41-4125-BBFB-ACB20152BA8F</PROPERTY>
							<!-- Windows CE (5.0) platform maps to the Windows CE (5.x) package -->
							<PROPERTY ID="E2BECB1F-8C8C-41BA-B736-9BE7D946A398">262076C4-CEF5-4324-99B5-9992F4B9E04E</PROPERTY>
						</PROPERTYCONTAINER>
					</FILE>
				</FILECONTAINER>

				<PACKAGECONTAINER>

				<!-- ********************************************************************************************************* -->
				<!--                                                                                                           -->
				<!-- SSCE 3.5 packages for installation onto devices running Compact Framework v2						   -->
				<!--                                                                                                           -->
				<!-- ********************************************************************************************************* -->

				<!--
				SQL Server Compact Edition - Windows Mobile, Pocket PC (4.x)
				Installs the main SSCE cab, the replication cab, and the developer strings cab.
				This installation is triggered by the following reference being added to the project:
				- System.Data.SqlServerCe, Version=3.5.0.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3
				-->
				<PACKAGE ID="A51B2C18-0718-4729-B80D-D98EA4B63758" Name="SQL Server Compact Edition installable CAB" Protected="true">
					<PROPERTYCONTAINER>
						<PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
					</PROPERTYCONTAINER>
					<PACKAGETYPECONTAINER>
						<PACKAGETYPE ID="ARMV4" Name="ARMV4" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce400\ARMV4\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.ppc.wce4.armv4.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.ppc.wce4.armv4.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_PPC_WCE4_DEV_ARMV4"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
					</PACKAGETYPECONTAINER>
				</PACKAGE>

				<!-- 
				SQL Server Compact Edition - Windows Mobile, Pocket PC (5.x)
				Installs the main SSCE cab, the replication cab, and the developer strings cab.
				This installation is triggered by the following reference being added to the project:
				- System.Data.SqlServerCe, Version=3.5.0.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3
				-->
					<PACKAGE ID="647BFB62-BFF0-40EB-849C-115F042B19C5" Name="SQL Server Compact Edition installable CAB" Protected="true">
					<PROPERTYCONTAINER>
						<PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
					</PROPERTYCONTAINER>
					<PACKAGETYPECONTAINER>
						<PACKAGETYPE ID="ARMV4i" Name="ARMV4i" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\ARMV4i\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">ARMV4i</PROPERTY>
								<PROPERTY ID="SkipIfInROM" Protected="true">true</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.ppc.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.ppc.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_PPC_WCE5_DEV_ARMV4I"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
					</PACKAGETYPECONTAINER>
				</PACKAGE>

				<!-- 
				SQL Server Compact Edition - Smartphone (5.x)
				Installs the main SSCE cab, the replication cab, and the developer strings cab.
				This installation is triggered by the following reference being added to the project:
				- System.Data.SqlServerCe, Version=3.5.0.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3
				-->
				<PACKAGE ID="8C8F2D09-DF3E-4F88-A967-7C012B25E0AC" Name="SQL Server Compact Edition installable CAB" Protected="true">
					<PROPERTYCONTAINER>
						<PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
					</PROPERTYCONTAINER>
					<PACKAGETYPECONTAINER>
						<PACKAGETYPE ID="ARMV4I" Name="ARMV4I" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\ARMV4I\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
								<PROPERTY ID="SkipIfInROM" Protected="true">true</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.phone.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.phone.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_PHONE_WCE5_DEV_ARMV4I"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
					</PACKAGETYPECONTAINER>
				</PACKAGE>
				
				<!-- 
				SQL Server Compact Edition - Windows CE (5.x )
				Installs the main SSCE cab, the replication cab, and the developer strings cab.
				This installation is triggered by the following reference being added to the project:
				- System.Data.SqlServerCe, Version=3.5.0.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3
				-->
					<PACKAGE ID="515B5EC3-0DDB-4C3F-8589-82EC5052297A" Name="SQL Server Compact Edition installable CAB" Protected="true">
					<PROPERTYCONTAINER>
						<PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
					</PROPERTYCONTAINER>
					<PACKAGETYPECONTAINER>
						<PACKAGETYPE ID="ARMV4I" Name="ARMV4I" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\ARMV4I\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_WCE5_DEV_ARMV4I"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="MIPSII" Name="MIPSII" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\MIPSII\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">MIPSII</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.wce5.mipsii.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.wce5.mipsii.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_WCE5_DEV_MIPSII"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="MIPSII_FP" Name="MIPSII_FP" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\MIPSII_FP\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">MIPSII_FP</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.wce5.mipsii_fp.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.wce5.mipsii_fp.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_WCE5_DEV_MIPSII_FP"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="MIPSIV" Name="MIPSIV" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\MIPSIV\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">MIPSIV</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.wce5.mipsiv.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.wce5.mipsiv.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_WCE5_DEV_MIPSIV"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="MIPSIV_FP" Name="MIPSIV_FP" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\MIPSIV_FP\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">MIPSIV_FP</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.wce5.mipsiv_fp.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.wce5.mipsiv_fp.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_WCE5_DEV_MIPSIV_FP"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="SH4" Name="SH4" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\SH4\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">SH4</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.wce5.sh4.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.wce5.sh4.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_WCE5_DEV_SH4"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="X86" Name="X86" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\wce500\X86\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">X86</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sqlce.wce5.x86.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<FILE ID="sqlce.repl.wce5.x86.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQLCE30_WCE5_DEV_X86"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
					</PACKAGETYPECONTAINER>
				</PACKAGE>

				<!-- ********************************************************************************************************* -->
				<!--                                                                                                           -->
				<!-- SQL Client v3.5 packages for installation onto devices running Compact Framework v2						   -->
				<!--                                                                                                           -->
				<!-- ********************************************************************************************************* -->

				<!--
				SQL Client 2.0 - Windows Mobile, Pocket PC (4.x)
				Installs the main SQL Client cab, and the developer strings cab.
				This installation is triggered by the following reference being added to the project:
				- System.Data.SqlClient, Version=3.0.3600.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3
				-->
				<PACKAGE ID="64C8FE35-C04C-4BCA-9B48-1D67112787A8" Name="SQL Server Compact Edition installable CAB" Protected="true">
					<PROPERTYCONTAINER>
						<PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
					</PROPERTYCONTAINER>
					<PACKAGETYPECONTAINER>
						<PACKAGETYPE ID="ARMV4" Name="ARMV4" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce400\ARMV4\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.ppc.wce4.armv4.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_PPC_WCE4_DEV_ARMV4"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
					</PACKAGETYPECONTAINER>
				</PACKAGE>

				<!-- 
				SQL Client 2.0 - Windows Mobile, Pocket PC (5.x)
				Installs the main SQL Client cab, and the developer strings cab.
				This installation is triggered by the following reference being added to the project:
				- System.Data.SqlClient, Version=3.0.3600.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3
				-->
				<PACKAGE ID="2BD213E1-4DF6-41AA-81DD-E8C5C9CAF16D" Name="SQL Server Compact Edition installable CAB" Protected="true">
					<PROPERTYCONTAINER>
						<PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
					</PROPERTYCONTAINER>
					<PACKAGETYPECONTAINER>
						<PACKAGETYPE ID="ARMV4i" Name="ARMV4i" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\ARMV4i\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">ARMV4i</PROPERTY>
								<PROPERTY ID="SkipIfInROM" Protected="true">true</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.ppc.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_PPC_WCE5_DEV_ARMV4I"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
					</PACKAGETYPECONTAINER>
				</PACKAGE>

				<!-- 
				SQL Client 2.0 - Smartphone (5.x)
				Installs the main SQL Client cab, and the developer strings cab.
				This installation is triggered by the following reference being added to the project:
				- System.Data.SqlClient, Version=3.0.3600.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3
				-->
				<PACKAGE ID="335E564D-9E41-4125-BBFB-ACB20152BA8F" Name="SQL Server Compact Edition installable CAB" Protected="true">
					<PROPERTYCONTAINER>
						<PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
					</PROPERTYCONTAINER>
					<PACKAGETYPECONTAINER>
						<PACKAGETYPE ID="ARMV4I" Name="ARMV4I" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\ARMV4I\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
								<PROPERTY ID="SkipIfInROM" Protected="true">true</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.phone.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_PHONE_WCE5_DEV_ARMV4I"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
					</PACKAGETYPECONTAINER>
				</PACKAGE>

				<!-- 
				SQL Client 2.0 - Windows CE (5.x )
				Installs the main SQL Client cab, and the developer strings cab.
				This installation is triggered by the following reference being added to the project:
				- System.Data.SqlClient, Version=3.0.3600.0, Culture=neutral, PublicKeyToken=3be235df1c8d2ad3
				-->
				<PACKAGE ID="262076C4-CEF5-4324-99B5-9992F4B9E04E" Name="SQL Server Compact Edition installable CAB" Protected="true">
					<PROPERTYCONTAINER>
						<PROPERTY ID="NDPVersion" Protected="true">v2.0.0.0</PROPERTY>
					</PROPERTYCONTAINER>
					<PACKAGETYPECONTAINER>
						<PACKAGETYPE ID="ARMV4I" Name="ARMV4I" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\ARMV4I\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.wce5.armv4i.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_WCE5_DEV_ARMV4I"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="MIPSII" Name="MIPSII" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\MIPSII\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">MIPSII</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.wce5.mipsii.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_WCE5_DEV_MIPSII"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="MIPSII_FP" Name="MIPSII_FP" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\MIPSII_FP\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">MIPSII_FP</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.wce5.mipsii_fp.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_WCE5_DEV_MIPSII_FP"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="MIPSIV" Name="MIPSIV" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\MIPSIV\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">MIPSIV</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.wce5.mipsiv.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_WCE5_DEV_MIPSIV"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="MIPSIV_FP" Name="MIPSIV_FP" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\MIPSIV_FP\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">MIPSIV_FP</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.wce5.mipsiv_fp.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_WCE5_DEV_MIPSIV_FP"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="SH4" Name="SH4" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\SH4\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">SH4</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.wce5.sh4.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_WCE5_DEV_SH4"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
						<PACKAGETYPE ID="X86" Name="X86" Protected="true">
							<PROPERTYCONTAINER>
								<PROPERTY ID="RemotePath" Protected="true">\windows\</PROPERTY>
								<PROPERTY ID="RootPath" Protected="true" >%CSIDL_PROGRAM_FILES%\Microsoft SQL Server Compact Edition\v3.5\Devices\Client\wce500\X86\</PROPERTY>
								<PROPERTY ID="CPU" Protected="true">X86</PROPERTY>
							</PROPERTYCONTAINER>
							<FILECONTAINER>
								<FILE ID="sql.wce5.x86.CAB">
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</FILE>
								<xsl:element name="FILE">
									<xsl:attribute name="ID">
										<xsl:value-of select="LANGUAGE/MICROSOFT_SQLCE_3_5/SQL_WCE5_DEV_X86"/>
									</xsl:attribute>
									<xsl:attribute name="Protected">true</xsl:attribute>
									<PROPERTYCONTAINER>
										<PROPERTY ID="Installer">\windows\wceload.exe</PROPERTY>
										<PROPERTY ID="InstallerPreOptions">/noui</PROPERTY>
									</PROPERTYCONTAINER>
								</xsl:element>
							</FILECONTAINER>
						</PACKAGETYPE>
					</PACKAGETYPECONTAINER>
				</PACKAGE>
				</PACKAGECONTAINER>
						
			</ADDON>
		</ADDONCONTAINER>
	</xsl:template>
</xsl:stylesheet>
