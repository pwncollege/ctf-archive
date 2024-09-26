This challenge cannot be solved on pwn.college majorly becuase of the space requerments for the kernelcache but it is still available.

Download kernelcache:
```
pzb -g kernelcache.release.iphone10 https://updates.cdn-apple.com/2022FallFCS/fullrestores/012-65931/BD2515B7-7802-4EB4-9377-98E3238EA5A8/iPhone_4.7_P3_16.0_20A362_Restore.ipsw
```

Extract kernelcache:
```
ipsw kernel dec kernelcache.release.iphone10
```

Patches:
```
Vulnerabilities: 
    IOSurfaceRootUserClient::lookup_surface_from_port()
        0xFFFFFFF005B27844: 0xF90002B4
        0xFFFFFFF005B27848: 0xD2800013
    IOSurface::setIndexedTimestamp()
        0xFFFFFFF005B1B83C: 0xF9000022
        0xFFFFFFF005B1B840: 0x52800000
```

Since the challenge is not downloaded on pwn.college so this challenge doesn't have anything.

`Note: This challenge was not solved during the CTF and we would appreciate any writeups`

---

**Author:** R3CTF/YUANHENGCTF Team 
