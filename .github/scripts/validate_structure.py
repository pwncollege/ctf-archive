#!/usr/bin/env python3
import os
import sys
import yaml

def fail(msg):
    print(f"{msg}")
    sys.exit(1)

def load_yaml(path):
    try:
        with open(path) as f:
            return yaml.safe_load(f)
    except Exception as e:
        fail(f"Failed to parse YAML at {path}: {e}")

# Checks dojo.yml
dojo_path = "dojo.yml"
if not os.path.isfile(dojo_path):
    fail("Missing top‐level dojo.yml")

dojo = load_yaml(dojo_path)
modules = dojo.get("modules")
if not isinstance(modules, list):
    fail("'modules' key in dojo.yml must be a list")

# Checks modules
for mod in modules:
    mod_id = mod.get("id")
    if not mod_id:
        fail("Each entry under 'modules' in dojo.yml must have an 'id' field")
    mod_folder = os.path.join(mod_id)
    if not os.path.isdir(mod_folder):
        fail(f"Module folder not found: {mod_id}")
    mod_yml = os.path.join(mod_folder, "module.yml")
    if not os.path.isfile(mod_yml):
        fail(f"Missing module.yml inside module folder: {mod_id}")

    # Checks challenges
    module_cfg = load_yaml(mod_yml)
    challenges = module_cfg.get("challenges")
    if not isinstance(challenges, list):
        fail(f"'challenges' in {mod_id}/module.yml must be a list")

    for chal in challenges:
        chal_id = chal.get("id")
        if not chal_id:
            fail(f"Each challenge in {mod_id}/module.yml must have an 'id' field")
        chal_folder = os.path.join(mod_id, chal_id)
        if not os.path.isdir(chal_folder):
            fail(f"Challenge folder missing: {chal_id} in module {mod_id}")
        for required in ("REHOST.md", "DESCRIPTION.md"):
            path = os.path.join(chal_folder, required)
            if not os.path.isfile(path):
                fail(f"Missing {required} in {chal_folder}")

print("Repository structure is valid.")
