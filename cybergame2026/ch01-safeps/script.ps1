$ErrorActionPreference = "SilentlyContinue"
Set-StrictMode -Off

$FLAG = Get-Content -Path "/flag" -TotalCount 1

Write-Host "Welcome to SAFEPS, hardened environment." -ForegroundColor Cyan

$Allowed = @("help","about","echo","time")

function Show-Help {
  Write-Host "Available commands:" -ForegroundColor Green
  $Allowed | ForEach-Object { Write-Host "  $_" }
}

function Show-About {
  Write-Host "SAFEPS" -ForegroundColor Green
}

function Run-JailCommand {
  param([string]$InputLine)

  if ([string]::IsNullOrWhiteSpace($InputLine)) { return }

  if ($InputLine.Length -gt 60) {
    Write-Host "Too long." -ForegroundColor Red
    return
  }

  $Bad = @(
    "import-module", "cp", "get-location", "new-itemproperty", "set-alias", "get-itempropertyvalue", "invoke-restmethod",
    "remove-job", "clear-variable", "get-help", "receive-job", "set-location", "select-string", "out-file",
    "get-command", "push-location", "add-content", "rename-item", "start-job", "gmo", "get-variable",
    "move-item", "get-itemproperty", "rm", "remove-variable", "invoke-expression", "get-content", "get-psdrive",
    "new-item", "set-item", "set-content", "get-childitem", "start-process", "get-item", "set-variable",
    "set-itemproperty", "invoke-command", "get-module", "get-alias", "clear-history", "wait-job", "pwd",
    "http", "https", "file", "ftp", "ftps", "data", 
    "sl", "rv", "sv", "ni", "si", "sp", "gp",
    "gci", "dir", "ls", "gc", "sc", "gv", "ac",
    "del", "erase", "rni", "cd", "pushd", "pop-location", "popd",
    "sls", "iwr", "irm", "iex", "icm", "gcm", "gal",
    "sal", "ipmo", "gl", "gi", "clhy", "saps", "sjob",
    "rcjb", "powershell", "pwsh", "flag", "sk", "cert", "ctf",
    "type", "cat", "reflection", "system.Reflection", "assembly", "System.AppDomain", "GetAssemblies",
    "LoadWithPartialName", "System.IO", "System.IO.File", "ReadAllText", "GetType", "System.Diagnostics.Process", "Start",
    "%", "?", "ac", "asnp", "cat", "cd", "CFS",
    "chdir", "clc", "clear", "clhy", "cli", "clp", "cls",
    "clv", "cnsn", "compare", "copy", "cp", "cpi", "cpp",
    "curl", "cvpa", "dbp", "del", "diff", "dir", "dnsn",
    "ebp", "epal", "epcsv", "epsn", "erase", "etsn",
    "exsn", "fc", "fhx", "fl", "foreach", "ft", "fw",
    "gal", "gbp", "gc", "gcb", "gci", "gcm", "gcs",
    "gdr", "ghy", "gi", "gin", "gjb", "gl", "gm",
    "gmo", "gp", "gps", "gpv", "group", "gsn", "gsnp",
    "gsv", "gtz", "gu", "gv", "gwmi", "history",
    "icm", "iex", "ihy", "ii", "ipal", "ipcsv", "ipmo",
    "ipsn", "irm", "ise", "iwmi", "iwr", "kill", "lp",
    "ls", "man", "md", "measure", "mi", "mount", "move",
    "mp", "mv", "nal", "ndr", "ni", "nmo", "npssc",
    "nsn", "nv", "ogv", "oh", "popd", "ps", "pushd",
    "pwd", "r", "rbp", "rcjb", "rcsn", "rd", "ag", 
    "ren", "ri", "rjb", "rm", "rmdir", "rmo", "rni",
    "rnp", "rp", "rsn", "rsnp", "rujb", "rv", "rvpa",
    "rwmi", "sajb", "sal", "saps", "sasv", "sbp", "sc",
    "scb", "select", "set", "shcm", "si", "sl", "sleep",
    "sls", "sort", "sp", "spjb", "spps", "spsv", "start",
    "stz", "sujb", "sv", "swmi", "tee", "trcm", "type",
    "wget", "where", "wjb", "write", "rdr"
  )


  $lower = $InputLine.ToLowerInvariant()
  foreach ($b in $Bad) {
    $b = $b.ToLowerInvariant()
    if ($lower -match [regex]::Escape($b)) {
        Write-Host "Nope." -ForegroundColor Red
        return
    }
  }

  $parts = $InputLine.TrimStart() -split '\s+', 2
  $first = $parts[0].ToLowerInvariant()
  if ($Allowed -notcontains $first) {
    Write-Host "Unknown command." -ForegroundColor Red
    return
  }

  switch ($first) {
    "help"  { Show-Help; return }
    "about" { Show-About; return }
    "time"  { Get-Date; return }

    "echo"  {
      $exprTrimmed = if ($parts.Count -gt 1) { $parts[1].Trim() } else { "" }
      if ([string]::IsNullOrWhiteSpace($exprTrimmed)) { return }

      if (
        $exprTrimmed -match '[_$=\-\.]' -or
        $exprTrimmed -match '[{}]' -or
        $exprTrimmed -match '[`]' -or
        $exprTrimmed -match '\$ExecutionContext' -or
        $exprTrimmed -match '\[' -or
        $exprTrimmed -match '\]'
      ) {
        Write-Host "Nope." -ForegroundColor Red
        return
      }

      if (
        ($exprTrimmed.Length -ge 2) -and
        (
          ($exprTrimmed.StartsWith("'") -and $exprTrimmed.EndsWith("'")) -or
          ($exprTrimmed.StartsWith('"') -and $exprTrimmed.EndsWith('"'))
        )
      ) {
        Write-Host $exprTrimmed.Substring(1, $exprTrimmed.Length - 2)
        return
      }

      if ($exprTrimmed -match '[^\u0000-\u007F]') {
        Write-Host "Nope." -ForegroundColor Red
        return
      }

      if ($exprTrimmed -match '^[a-zA-Z0-9_]+$') {
        Write-Host $exprTrimmed
        return
      }

      $sb = [ScriptBlock]::Create($exprTrimmed)
      $result = & $sb
      if ($null -ne $result) { $result }
      return
    }
  }
}

while (($line = [Console]::In.ReadLine()) -ne $null) {
    if ($line -eq "exit") { break }

    try {
        Run-JailCommand -InputLine $line
    } catch {
        Write-Host ("Error: " + $_.Exception.Message)
    }
}
