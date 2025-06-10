#!/usr/bin/env python3
import os
import re
import sys
import yaml

PATTERN = re.compile(r'^[a-z0-9-]{1,32}$')

def fail(msg):
    print(f"{msg}")
    sys.exit(1)

def load_yaml(path):
    try:
        with open(path, 'r') as f:
            return yaml.safe_load(f)
    except Exception as e:
        fail(f"Failed to parse YAML at {path}: {e}")
dojo_cfg = load_yaml('dojo.yml')
if not isinstance(dojo_cfg.get('modules'), list):
    fail("'modules' key in dojo.yml must be a list")
module_ids = [m.get('id') for m in dojo_cfg['modules']]

for mod_id in module_ids:
    if mod_id is None:
        fail("Found a module entry without an 'id'")
    if not PATTERN.match(mod_id):
        fail(f"Module ID '{mod_id}' does not match ^[a-z0-9-]{{1,32}}$")
    if not os.path.isdir(mod_id):
        fail(f"Module folder not found: '{mod_id}'")

    mod_yml = os.path.join(mod_id, 'module.yml')
    module_cfg = load_yaml(mod_yml)
    if not isinstance(module_cfg.get('challenges'), list):
        fail(f"'challenges' in {mod_yml} must be a list")
    chal_ids = [c.get('id') for c in module_cfg['challenges']]
    for chal_id in chal_ids:
        if chal_id is None:
            fail(f"A challenge in {mod_id}/module.yml is missing an 'id'")
        if not PATTERN.match(chal_id):
            fail(f"Challenge ID '{chal_id}' in module '{mod_id}' does not match ^[a-z0-9-]{{1,32}}$")
        chal_folder = os.path.join(mod_id, chal_id)
        if not os.path.isdir(chal_folder):
            fail(f"Challenge folder not found: '{chal_folder}'")

print("All module and challenge folder names conform to the regex.")
