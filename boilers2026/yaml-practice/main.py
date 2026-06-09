import secrets
from pathlib import Path

import yaml
from prefect import flow, task
from starlette.applications import Starlette
from starlette.datastructures import UploadFile
from starlette.requests import Request
from starlette.responses import JSONResponse
from starlette.routing import Route
from starlette.templating import Jinja2Templates

BASE_DIR = Path(__file__).resolve().parent
UPLOAD_DIR = BASE_DIR / "uploads"
TEMPLATE_DIR = BASE_DIR / "templates"
ALLOWED_EXTENSION = ".yml"

UPLOAD_DIR.mkdir(exist_ok=True)
TEMPLATE_DIR.mkdir(exist_ok=True)
templates = Jinja2Templates(directory=str(TEMPLATE_DIR))


@task
def validate_yaml_task(file_path: Path) -> bool:
    try:
        yaml.safe_load(file_path.read_text())
        return True
    except yaml.YAMLError:
        return False


@flow(name="validate-yaml")
def validate_yaml_flow(file_path: Path) -> bool:
    return validate_yaml_task(file_path)


def check_filename(filename: str) -> str:
    if Path(filename).suffix.lower() != ALLOWED_EXTENSION:
        raise ValueError("Only .yml files are allowed.")
    return filename


async def homepage(request: Request):
    return templates.TemplateResponse(
        request,
        "index.html",
        {
            "page_title": "YAML Validator",
        },
    )


async def upload_yaml(request: Request) -> JSONResponse:
    form = await request.form()
    uploaded_file = form["file"]
    if not isinstance(uploaded_file, UploadFile):
        return JSONResponse(
            {"success": False, "error": "Expected a file upload."}, status_code=400
        )

    filename = uploaded_file.filename
    contents = await uploaded_file.read()

    if filename is None:
        filename = secrets.token_hex(16) + ".yaml"

    try:
        safe_name = check_filename(filename)
    except ValueError:
        return JSONResponse(
            {"success": False, "error": "Bad filename."}, status_code=400
        )

    if len(contents) > 200:
        return JSONResponse(
            {"success": False, "error": "File too large."}, status_code=400
        )

    try:
        decoded_contents = contents.decode("utf-8")
    except UnicodeDecodeError:
        return JSONResponse(
            {"success": False, "error": "File must be valid UTF-8 text."},
            status_code=400,
        )

    file_path = UPLOAD_DIR / safe_name
    file_path.write_text(decoded_contents)

    return JSONResponse(
        {
            "success": True,
            "filename": safe_name,
        }
    )


async def validate_yaml(request: Request) -> JSONResponse:
    data = await request.json()
    filename = data.get("filename")

    if filename is None:
        return JSONResponse(
            {"ok": False, "error": "Missing filename."}, status_code=400
        )
    try:
        safe_name = check_filename(Path(filename).name)
    except ValueError:
        return JSONResponse(
            {"ok": False, "error": "Invalid filename."}, status_code=400
        )

    file_path = UPLOAD_DIR / safe_name
    if not file_path.is_file():
        return JSONResponse(
            {"ok": False, "error": "File not found."},
            status_code=404,
        )

    result = validate_yaml_flow(file_path)

    return JSONResponse(
        {
            "ok": True,
            "valid": result,
        }
    )


app = Starlette(
    routes=[
        Route("/", homepage),
        Route("/upload", upload_yaml, methods=["POST"]),
        Route("/validate", validate_yaml, methods=["POST"]),
    ]
)

if __name__ == "__main__":
    import uvicorn

    uvicorn.run(app, host="0.0.0.0", port=8000)
