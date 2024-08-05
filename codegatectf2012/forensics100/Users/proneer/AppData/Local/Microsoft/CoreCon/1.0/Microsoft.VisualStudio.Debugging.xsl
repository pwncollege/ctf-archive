<?xml version="1.0" standalone="no"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="/">

<ADDONCONTAINER>

<ADDON Priority="110">

<DEBUGGERCONTAINER>

<!-- EPS process list -->
<DEBUGGER ID="ProcessList">
  <PROPERTYCONTAINER>
    <PROPERTY ID="default">
      <PROPERTYCONTAINER>
        <PROPERTY ID="default">nk.exe;filesys.exe;device.exe;gwes.exe;shell.exe;explorer.exe;edbgjit.exe;edm.exe;edm2.exe;</PROPERTY>
      </PROPERTYCONTAINER>
    </PROPERTY>
  </PROPERTYCONTAINER>
</DEBUGGER>

<!-- Debug Engine Map -->
<DEBUGGER ID="DebugEngineMap">

<DETYPECONTAINER>

	<!-- Managed type -->
	<DETYPE ID="E5D9D993-FD9F-4c3b-AEC7-DAE1D48F4AF1">
	<PROPERTYCONTAINER>

		<!-- Awaiting version guids -->

		<!-- NetCF 1.0 -->
		<PROPERTY ID="v1.0">
		<PROPERTYCONTAINER>
			<PROPERTY ID="DE">123D150B-FA18-461C-B218-45B3E4589F9B</PROPERTY>
			<PROPERTY ID="DEv2">8C37B683-C921-4076-AC47-EC6DA03FA658</PROPERTY>
			<PROPERTY ID="PS">2D32AA54-1F84-4964-BC13-ECB871943797</PROPERTY>
		</PROPERTYCONTAINER>
                </PROPERTY>

		<!-- NetCF 2.0 -->
		<PROPERTY ID="v2.0">
		<PROPERTYCONTAINER>
			<PROPERTY ID="DE">8C37B683-C921-4076-AC47-EC6DA03FA658</PROPERTY>
			<PROPERTY ID="PS">2D32AA54-1F84-4964-BC13-ECB871943797</PROPERTY>
		</PROPERTYCONTAINER>
		</PROPERTY>


		<!-- NetCF 3.5 -->
		<PROPERTY ID="v3.5">
		<PROPERTYCONTAINER>
			<PROPERTY ID="DE">8C37B683-C921-4076-AC47-EC6DA03FA658</PROPERTY>
			<PROPERTY ID="PS">2D32AA54-1F84-4964-BC13-ECB871943797</PROPERTY>
		</PROPERTYCONTAINER>
		</PROPERTY>

		<!-- We default to platform if NDP version is not found -->

		<!-- Windows CE 4.2 == NetCF 2.0 -->
		<PROPERTY ID="E2BECB1F-8C8C-41ba-B736-9BE7D946A398">
		<PROPERTYCONTAINER>
			<PROPERTY ID="DE">8C37B683-C921-4076-AC47-EC6DA03FA658</PROPERTY>
			<PROPERTY ID="PS">2D32AA54-1F84-4964-BC13-ECB871943797</PROPERTY>
		</PROPERTYCONTAINER>
		</PROPERTY>
	</PROPERTYCONTAINER>
	</DETYPE>

	<!-- Native type -->
	<DETYPE ID="4C6F37BB-02E3-4173-B583-8AEA24D171AC">
	<PROPERTYCONTAINER>

		<!-- Windows CE 4.2 -->
		<PROPERTY ID="E2BECB1F-8C8C-41ba-B736-9BE7D946A398">
		<PROPERTYCONTAINER>
			<PROPERTY ID="DE">86ACAEC6-D3D4-47a5-9017-90E5202671A7</PROPERTY>
			<PROPERTY ID="PS">2D32AA54-1F84-4964-BC13-ECB871943797</PROPERTY>
		</PROPERTYCONTAINER>
		</PROPERTY>

		<!-- Pocket PC 2003 Platform -->
		<PROPERTY ID="3C41C503-53EF-4c2a-8DD4-A8217CAD115E">
		<PROPERTYCONTAINER>
			<PROPERTY ID="DE">86ACAEC6-D3D4-47a5-9017-90E5202671A7</PROPERTY>
			<PROPERTY ID="PS">2D32AA54-1F84-4964-BC13-ECB871943797</PROPERTY>
		</PROPERTYCONTAINER>
		</PROPERTY>

	</PROPERTYCONTAINER>
	</DETYPE>

	<!-- Interop type -->
	<DETYPE ID="6E94C26C-2BF0-40a9-9F2F-240064BFFB35">
	<PROPERTYCONTAINER>
	</PROPERTYCONTAINER>
	</DETYPE>

</DETYPECONTAINER>

<PROPERTYCONTAINER>
</PROPERTYCONTAINER>

</DEBUGGER>

<!-- Symbol handler OS map -->
<DEBUGGER ID="SymbolHandlerOSMap">
<OSCONTAINER>
  <!-- PLATFORM_WIN32_NT -->
  <OS ID = "2">
    <VERSIONCONTAINER>
    <!--Windows2000, XP and Windows .Net server 2003 family -->
      <VERSION ID = "5">
        <VERSIONCONTAINER>

          <!-- Windows XP -->

          <VERSION ID = "1">
            <PROPERTYCONTAINER>

               <!-- Architecture -->

               <!-- Intel Architecture -->
               <PROPERTY ID="0">

                 <!-- Processor level -->
                 <PROPERTYCONTAINER>

                   <!-- level 9 -->
                   <PROPERTY ID = "9">

                     <!-- Revision -->
                     <PROPERTYCONTAINER>
                       <PROPERTY ID="516">I386</PROPERTY>
                       <PROPERTY ID="default" Protected="true">I386</PROPERTY>
                     </PROPERTYCONTAINER>

                   </PROPERTY>

                   <!-- default level -->
                   <PROPERTY ID="default" Protected="true">I386</PROPERTY>

                 </PROPERTYCONTAINER>

               </PROPERTY>

               <!-- Default Architecture -->
               <PROPERTY ID="default" Protected="true">I386</PROPERTY>

             </PROPERTYCONTAINER>
          </VERSION>
          <!-- Windows NT 2000 -->

          <VERSION ID = "0">
            <PROPERTYCONTAINER>

               <!-- Architecture -->

               <!-- Intel Architecture -->
               <PROPERTY ID="0">

                 <!-- Processor level -->
                 <PROPERTYCONTAINER>

                   <!-- level 9 -->
                   <PROPERTY ID = "9">

                     <!-- Revision -->
                     <PROPERTYCONTAINER>
                       <PROPERTY ID="516">I386</PROPERTY>
                       <PROPERTY ID="default" Protected="true">I386</PROPERTY>
                     </PROPERTYCONTAINER>

                   </PROPERTY>

                   <!-- default level -->
                   <PROPERTY ID="default" Protected="true">I386</PROPERTY>

                 </PROPERTYCONTAINER>

               </PROPERTY>

               <!-- Default Architecture -->
               <PROPERTY ID="default" Protected="true">I386</PROPERTY>

             </PROPERTYCONTAINER>
          </VERSION>

        </VERSIONCONTAINER>
      </VERSION>
    </VERSIONCONTAINER>
  </OS>
  <!-- PLATFORM_WINCE -->
	<OS ID="3">
		<VERSIONCONTAINER>
			<!-- Windows CE 4.x -->
			<VERSION ID="4">
				<VERSIONCONTAINER>
					<!-- Windows CE 4.20 -->
					<VERSION ID="20">
							
							<PROPERTYCONTAINER>
								<!-- Architecture Id -->
								<!-- ARM -->
								<PROPERTY ID="5">
									<PROPERTYCONTAINER>
										<!--Core Id -->
										<PROPERTY ID="1">
											<PROPERTYCONTAINER>
												<!-- Feature Id -->
												<!-- InstructionSet = 83951616 -->
												<PROPERTY ID="0">ARMV4</PROPERTY>
												<!-- InstructionSet = 83951617 -->
												<PROPERTY ID="1">ARMV4</PROPERTY>
												<PROPERTY ID="default" Protected="true">ARMV4</PROPERTY>
											</PROPERTYCONTAINER>
										</PROPERTY>
										<!--Core Id -->
										<PROPERTY ID="2">
											<PROPERTYCONTAINER>
												<!-- Feature Id -->
												<!-- InstructionSet = 84082688 -->
												<PROPERTY ID="0">ARMV4I</PROPERTY>
												<!-- InstructionSet = 84082689 -->
												<PROPERTY ID="1">ARMV4I</PROPERTY>
												<PROPERTY ID="default" Protected="true">ARMV4I</PROPERTY>
											</PROPERTYCONTAINER>
										</PROPERTY>
										<!--Core Id -->
										<PROPERTY ID="default" Protected="true">ARMV4I</PROPERTY>

									</PROPERTYCONTAINER>
								</PROPERTY>
							

								<!-- MIPS -->
								<PROPERTY ID="1">
									<PROPERTYCONTAINER>
										<!--Core Id -->
										<PROPERTY ID="1">
											<PROPERTYCONTAINER>
												<!-- Feature Id -->
												<!-- InstructionSet = 16842752 -->
												<PROPERTY ID="0">MIPSII</PROPERTY>
												<PROPERTY ID="default" Protected="true">MIPSII</PROPERTY>
											</PROPERTYCONTAINER>
										</PROPERTY>
										<!--Core Id -->
										<PROPERTY ID="2">
											<PROPERTYCONTAINER>
												<!-- Feature Id -->
												<!-- InstructionSet = 16908288 -->
												<PROPERTY ID="0">MIPSII</PROPERTY>
												<!-- InstructionSet = 16908289 -->
												<PROPERTY ID="1">MIPSII_FP</PROPERTY>
												<PROPERTY ID="default" Protected="true">MIPSII_FP</PROPERTY>
											</PROPERTYCONTAINER>
										</PROPERTY>
										<!--Core Id -->
										<PROPERTY ID="3">
											<PROPERTYCONTAINER>
												<!-- Feature Id -->
												<!-- InstructionSet = 16973824 -->
												<PROPERTY ID="0">MIPSIV</PROPERTY>
												<!-- InstructionSet = 16973825 -->
												<PROPERTY ID="1">MIPSIV_FP</PROPERTY>
												<PROPERTY ID="default" Protected="true">MIPSIV_FP</PROPERTY>
											</PROPERTYCONTAINER>
										<!--Core Id -->
										<PROPERTY ID="default" Protected="true">MIPSII_FP</PROPERTY>
										</PROPERTY>
									</PROPERTYCONTAINER>
								</PROPERTY>

								<!-- SHx -->
								<PROPERTY ID="4">
									<PROPERTYCONTAINER>
										<!--Core Id -->
										<PROPERTY ID="2">
											<PROPERTYCONTAINER>
												<!-- Feature Id -->
												<!-- InstructionSet = 67239937 -->
												<PROPERTY ID="1">SH4</PROPERTY>
												<PROPERTY ID="default" Protected="true">SH4</PROPERTY>
											</PROPERTYCONTAINER>
										<!--Core Id -->
										<PROPERTY ID="default" Protected="true">SH4</PROPERTY>
										</PROPERTY>
									</PROPERTYCONTAINER>
								</PROPERTY>
								<!-- X86 -->
								<PROPERTY ID="0">
									<PROPERTYCONTAINER>
										<!--Core Id -->
										<PROPERTY ID="1">
											<PROPERTYCONTAINER>
												<!-- Feature Id -->
												<!-- InstructionSet = 65537 -->
												<PROPERTY ID="1">X86</PROPERTY>
												<PROPERTY ID="default" Protected="true">X86</PROPERTY>
											</PROPERTYCONTAINER>
										</PROPERTY>
										<!--Core Id -->
										<PROPERTY ID="default" Protected="true">X86</PROPERTY>
									</PROPERTYCONTAINER>
								</PROPERTY>
							</PROPERTYCONTAINER>
						</VERSION>
					</VERSIONCONTAINER>
				</VERSION>
		</VERSIONCONTAINER>
	</OS>
</OSCONTAINER>

<PROPERTYCONTAINER>
</PROPERTYCONTAINER>

</DEBUGGER>

<!-- Symbol Handler CPU map -->
<DEBUGGER ID="SymbolHandlerCPUMap">

<SHCPUMAPCONTAINER>

	<SHCPUMAP ID="X86">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_x86_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">true</PROPERTY>

		<!-- 33 == CV_REG_EIP -->
		<PROPERTY ID="_RegisterIndex_PC">33</PROPERTY>
		<!-- 21 == CV_REG_ESP -->
		<PROPERTY ID="_RegisterIndex_SP">21</PROPERTY>
		<!-- 33 == CV_REG_EIP -->
		<PROPERTY ID="_RegisterIndex_RA">33</PROPERTY>

		<PROPERTY ID="_HasPDATA">false</PROPERTY>
		<PROPERTY ID="_PDATAFormat"/>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">false</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit"/>
		<!-- OpCode is 0xcc -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">1</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">20</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">1</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize">6</PROPERTY>
		<PROPERTY ID="_IsVariableInstructionSize">true</PROPERTY>
		<!-- 32 bit instruction address InstructionAddressMask = 0x00000000FFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">4294967295</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>

	<SHCPUMAP ID="I386">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_i386_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">true</PROPERTY>

		<!-- 33 == CV_REG_EIP -->
		<PROPERTY ID="_RegisterIndex_PC">33</PROPERTY>
		<!-- 21 == CV_REG_ESP -->
		<PROPERTY ID="_RegisterIndex_SP">21</PROPERTY>
		<!-- 33 == CV_REG_EIP -->
		<PROPERTY ID="_RegisterIndex_RA">33</PROPERTY>

		<PROPERTY ID="_HasPDATA">false</PROPERTY>
		<PROPERTY ID="_PDATAFormat"/>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">false</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit"/>
		<!-- OpCode is 0xcc -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">1</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">20</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">1</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize">6</PROPERTY>
		<PROPERTY ID="_IsVariableInstructionSize">true</PROPERTY>
		<!-- 32 bit instruction address InstructionAddressMask = 0x00000000FFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">4294967295</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>

	<SHCPUMAP ID="ARMV4I">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_armv4i_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">false</PROPERTY>

		<!-- 25 == CV_ARM_PC -->
		<PROPERTY ID="_RegisterIndex_PC">25</PROPERTY>
		<!-- 23 == CV_ARM_SP -->
		<PROPERTY ID="_RegisterIndex_SP">23</PROPERTY>
		<!-- 24 == CV_ARM_LR -->
		<PROPERTY ID="_RegisterIndex_RA">24</PROPERTY>

		<PROPERTY ID="_HasPDATA">true</PROPERTY>
		<!-- 4 == COMPRESSED -->
		<PROPERTY ID="_PDATAFormat">4</PROPERTY>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">true</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit"/>
		<!-- OpCode is 0xE6000010 -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">4</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">4</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">2</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize"/>
		<PROPERTY ID="_IsVariableInstructionSize">false</PROPERTY>
		<!-- 32 bit instruction address InstructionAddressMask = 0x00000000FFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">4294967295</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>

	<SHCPUMAP ID="ARMV4">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_armv4_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">false</PROPERTY>

		<!-- 25 == CV_ARM_PC -->
		<PROPERTY ID="_RegisterIndex_PC">25</PROPERTY>
		<!-- 23 == CV_ARM_SP -->
		<PROPERTY ID="_RegisterIndex_SP">23</PROPERTY>
		<!-- 24 == CV_ARM_LR -->
		<PROPERTY ID="_RegisterIndex_RA">24</PROPERTY>

		<PROPERTY ID="_HasPDATA">true</PROPERTY>
		<!-- 4 == COMPRESSED -->
		<PROPERTY ID="_PDATAFormat">4</PROPERTY>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">true</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit">32</PROPERTY>
		<!-- OpCode is 0xE6000010 -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">4</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">4</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">4</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize"/>
		<PROPERTY ID="_IsVariableInstructionSize">false</PROPERTY>
		<!-- 32 bit instruction address InstructionAddressMask = 0x00000000FFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">4294967295</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>


	<SHCPUMAP ID="MIPSII_FP">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_mipsii_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">false</PROPERTY>

		<!-- 50 == CV_M4_Fir -->
		<PROPERTY ID="_RegisterIndex_PC">50</PROPERTY>
		<!-- 39 == CV_M4_IntSP -->
		<PROPERTY ID="_RegisterIndex_SP">39</PROPERTY>
		<!-- 41 == CV_M4_IntRA -->
		<PROPERTY ID="_RegisterIndex_RA">41</PROPERTY>

		<PROPERTY ID="_HasPDATA">true</PROPERTY>
		<!-- 6 == MIXED -->
		<PROPERTY ID="_PDATAFormat">6</PROPERTY>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">true</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit"/>
		<!-- OpCode is 0x0016000D -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">4</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">4</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">2</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize"/>
		<PROPERTY ID="_IsVariableInstructionSize">false</PROPERTY>
		<!-- 32 bit instruction address InstructionAddressMask = 0x00000000FFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">4294967295</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>

	<SHCPUMAP ID="MIPSII">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_mipsii_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">false</PROPERTY>

		<!-- 50 == CV_M4_Fir -->
		<PROPERTY ID="_RegisterIndex_PC">50</PROPERTY>
		<!-- 39 == CV_M4_IntSP -->
		<PROPERTY ID="_RegisterIndex_SP">39</PROPERTY>
		<!-- 41 == CV_M4_IntRA -->
		<PROPERTY ID="_RegisterIndex_RA">41</PROPERTY>

		<PROPERTY ID="_HasPDATA">true</PROPERTY>
		<!-- 6 == MIXED -->
		<PROPERTY ID="_PDATAFormat">6</PROPERTY>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">true</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit"/>
		<!-- OpCode is 0x0016000D -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">4</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">4</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">2</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize"/>
		<PROPERTY ID="_IsVariableInstructionSize">false</PROPERTY>
		<!-- 32 bit instruction address InstructionAddressMask = 0x00000000FFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">4294967295</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>

	<SHCPUMAP ID="MIPSIV">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_mipsiv_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">false</PROPERTY>

		<!-- 50 == CV_M4_Fir -->
		<PROPERTY ID="_RegisterIndex_PC">50</PROPERTY>
		<!-- 39 == CV_M4_IntSP -->
		<PROPERTY ID="_RegisterIndex_SP">39</PROPERTY>
		<!-- 41 == CV_M4_IntRA -->
		<PROPERTY ID="_RegisterIndex_RA">41</PROPERTY>

		<PROPERTY ID="_HasPDATA">true</PROPERTY>
		<!-- 6 == MIXED -->
		<PROPERTY ID="_PDATAFormat">6</PROPERTY>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">true</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit"/>
		<!-- OpCode is 0x0016000D -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">4</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">4</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">2</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize"/>
		<PROPERTY ID="_IsVariableInstructionSize">false</PROPERTY>
		<!-- 64 bit instruction address InstructionAddressMask = 0xFFFFFFFFFFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">-1</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>

	<SHCPUMAP ID="MIPSIV_FP">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_mipsiv_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">false</PROPERTY>

		<!-- 50 == CV_M4_Fir -->
		<PROPERTY ID="_RegisterIndex_PC">50</PROPERTY>
		<!-- 39 == CV_M4_IntSP -->
		<PROPERTY ID="_RegisterIndex_SP">39</PROPERTY>
		<!-- 41 == CV_M4_IntRA -->
		<PROPERTY ID="_RegisterIndex_RA">41</PROPERTY>

		<PROPERTY ID="_HasPDATA">true</PROPERTY>
		<!-- 6 == MIXED -->
		<PROPERTY ID="_PDATAFormat">6</PROPERTY>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">true</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit"/>
		<!-- OpCode is 0x0016000D -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">4</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">4</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">2</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize"/>
		<PROPERTY ID="_IsVariableInstructionSize">false</PROPERTY>
		<!-- 64 bit instruction address InstructionAddressMask = 0xFFFFFFFFFFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">-1</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>

	<SHCPUMAP ID="SH4">
	<PROPERTYCONTAINER>
		<PROPERTY ID="_HelperLibFileName" _UseVSRelativePath="true">SmartDevices\Debugger\Bin\ESymP_sh4_hlp.dll</PROPERTY>
		<PROPERTY ID="_IsDiaStyle">false</PROPERTY>

		<!-- 50 == CV_SH3_Pc -->
		<PROPERTY ID="_RegisterIndex_PC">50</PROPERTY>
		<!-- 25 == CV_SH3_IntSp -->
		<PROPERTY ID="_RegisterIndex_SP">25</PROPERTY>
		<!-- 39 == CV_SH3_Pr -->
		<PROPERTY ID="_RegisterIndex_RA">39</PROPERTY>

		<PROPERTY ID="_HasPDATA">true</PROPERTY>
		<!-- 4 == COMPRESSED -->
		<PROPERTY ID="_PDATAFormat">4</PROPERTY>
		<PROPERTY ID="_UsingAddressToFindInstructionSize">false</PROPERTY>

		<PROPERTY ID="_InstructionSizeInBit">16</PROPERTY>
		<!-- OpCode is 0xC301 -->
		<PROPERTY ID="_DefaultBreakPointInstructionSize">2</PROPERTY>

		<PROPERTY ID="_InstructionMaxSize">2</PROPERTY>
		<PROPERTY ID="_InstructionMinSize">2</PROPERTY>
		<PROPERTY ID="_InstructionAverageSize"/>
		<PROPERTY ID="_IsVariableInstructionSize">false</PROPERTY>
		<!-- 32 bit instruction address InstructionAddressMask = 0x00000000FFFFFFFF -->
		<PROPERTY ID="_InstructionAddressMask">4294967295</PROPERTY>
	</PROPERTYCONTAINER>
	</SHCPUMAP>

</SHCPUMAPCONTAINER>

<PROPERTYCONTAINER>
</PROPERTYCONTAINER>

</DEBUGGER>

</DEBUGGERCONTAINER>

<PACKAGECONTAINER>

<!-- edm.exe package -->
<PACKAGE ID="AEF7671F-A8D6-4e27-8B83-6C3E44425B6E">

<PROPERTYCONTAINER>
</PROPERTYCONTAINER>

<PACKAGETYPECONTAINER>

<PACKAGETYPE Name="ARMV4" ID="ARMV4" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\armv4</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\armv4i</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="MIPSII" ID="MIPSII" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\mipsii</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">MIPSII</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="MIPSII_FP" ID="MIPSII_FP" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\mipsii</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">MIPSII_FP</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="MIPSIV" ID="MIPSIV" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\mipsiv</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">MIPSIV</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="MIPSIV_FP" ID="MIPSIV_FP" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\mipsiv</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">MIPSIV_FP</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="SH4" ID="SH4" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\sh4</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">SH4</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="X86" ID="X86" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\x86</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">X86</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

</PACKAGETYPECONTAINER>

</PACKAGE>

<!-- edm.exe package for Orcas VS  9.0 -->
<PACKAGE ID="7599EDA7-D314-45B9-963C-DEDA876D9A37">

<PROPERTYCONTAINER>
</PROPERTYCONTAINER>

<PACKAGETYPECONTAINER>

<PACKAGETYPE Name="ARMV4" ID="ARMV4" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\armv4</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">ARMV4</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm2.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm2.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="ARMV4I" ID="ARMV4I" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\armv4i</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">ARMV4I</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm2.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm2.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="MIPSII" ID="MIPSII" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\mipsii</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">MIPSII</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm2.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm2.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="MIPSII_FP" ID="MIPSII_FP" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\mipsii</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">MIPSII_FP</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm2.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm2.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="MIPSIV" ID="MIPSIV" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\mipsiv</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">MIPSIV</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm2.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm2.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="MIPSIV_FP" ID="MIPSIV_FP" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\mipsiv</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">MIPSIV_FP</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm2.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm2.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="SH4" ID="SH4" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\sh4</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">SH4</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm2.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm2.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

<PACKAGETYPE Name="X86" ID="X86" Protected="true">
  <PROPERTYCONTAINER>
    <PROPERTY ID="RemotePath" Protected="true">%CSIDL_WINDOWS%</PROPERTY>
    <PROPERTY ID="RootPath" _UseVSRelativePath="true" Protected="true">smartdevices\debugger\Target\wce400\x86</PROPERTY>
    <PROPERTY ID="CPU" Protected="true">X86</PROPERTY>
    <PROPERTY ID="CommandLine" Protected="true">%CSIDL_WINDOWS%\edbgtl.dll</PROPERTY>
    <PROPERTY ID="Host" Protected="true">edm2.exe</PROPERTY>
  </PROPERTYCONTAINER>
  <FILECONTAINER>
    <FILE ID="edm2.exe"/>
  </FILECONTAINER>
</PACKAGETYPE>

</PACKAGETYPECONTAINER>


</PACKAGE>





</PACKAGECONTAINER>

</ADDON>

</ADDONCONTAINER>

</xsl:template>
</xsl:stylesheet>
