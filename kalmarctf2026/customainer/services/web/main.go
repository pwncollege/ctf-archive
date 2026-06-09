package main

import (
	"bytes"
	"crypto/rand"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"mime/multipart"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"time"

	"github.com/gorilla/mux"
)

type CustomainerService struct {
	extractorURL  string
	allowedImages map[string][]string
}

type BuildRequest struct {
	BaseImage string `json:"base_image"`
}

type ExtractorResponse struct {
	Success bool   `json:"success"`
	Message string `json:"message"`
}

func NewCustomainerService() (*CustomainerService, error) {
	extractorURL := os.Getenv("EXTRACTOR_URL")
	if extractorURL == "" {
		extractorURL = "http://customainer-extractor:5000"
	}

	allowedImages := map[string][]string{
		"alpine": {"latest", "3.18", "3.17", "3.16"},
		"debian": {"stable-slim", "bullseye-slim", "bookworm-slim"},
	}

	return &CustomainerService{
		extractorURL:  extractorURL,
		allowedImages: allowedImages,
	}, nil
}

func (cs *CustomainerService) validateBaseImage(image string) bool {
	for _, r := range image {
		if r < 32 || r > 126 {
			return false
		}
	}

	for baseName, tags := range cs.allowedImages {
		for _, tag := range tags {
			allowedPattern := baseName + ":" + tag
			if strings.HasSuffix(image, allowedPattern) {
				return true
			}
		}
	}
	return false
}

func (cs *CustomainerService) handleUpload(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	err := r.ParseMultipartForm(32 << 20)
	if err != nil {
		http.Error(w, "Failed to parse form", http.StatusBadRequest)
		return
	}

	file, _, err := r.FormFile("archive")
	if err != nil {
		http.Error(w, "No archive file provided", http.StatusBadRequest)
		return
	}
	defer file.Close()

	baseImage := r.FormValue("base_image")
	if baseImage == "" {
		http.Error(w, "No base image specified", http.StatusBadRequest)
		return
	}

	if !cs.validateBaseImage(baseImage) {
		http.Error(w, "Invalid base image. Must be one of the allowed images.", http.StatusBadRequest)
		return
	}

	randomBytes := make([]byte, 16)
	_, err = rand.Read(randomBytes)
	if err != nil {
		http.Error(w, "Failed to generate job ID", http.StatusInternalServerError)
		return
	}
	jobID := "job_" + hex.EncodeToString(randomBytes)

	archiveData, err := io.ReadAll(file)
	if err != nil {
		http.Error(w, "Failed to read archive", http.StatusInternalServerError)
		return
	}

	err = cs.callExtractor(jobID, archiveData)
	if err != nil {
		cs.cleanup(jobID)
		http.Error(w, "Failed to extract archive: "+err.Error(), http.StatusInternalServerError)
		return
	}

	imageData, err := cs.buildAndExportImage(jobID, baseImage)
	if err != nil {
		cs.cleanup(jobID)
		http.Error(w, "Failed to build image: "+err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/x-tar")
	w.Header().Set("Content-Disposition", "attachment; filename=\"custom-image.tar\"")
	w.Write(imageData)

	cs.cleanup(jobID)
}

func (cs *CustomainerService) callExtractor(jobID string, archiveData []byte) error {
	var requestBody bytes.Buffer
	writer := multipart.NewWriter(&requestBody)

	part, err := writer.CreateFormFile("archive", "archive.tar.gz")
	if err != nil {
		return err
	}
	part.Write(archiveData)

	err = writer.WriteField("job_id", jobID)
	if err != nil {
		return err
	}

	writer.Close()

	req, err := http.NewRequest("POST", cs.extractorURL+"/extract", &requestBody)
	if err != nil {
		return err
	}
	req.Header.Set("Content-Type", writer.FormDataContentType())

	client := &http.Client{Timeout: 30 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("extractor returned status %d", resp.StatusCode)
	}

	var extractorResp ExtractorResponse
	err = json.NewDecoder(resp.Body).Decode(&extractorResp)
	if err != nil {
		return err
	}

	if !extractorResp.Success {
		return fmt.Errorf("extraction failed: %s", extractorResp.Message)
	}

	return nil
}

func (cs *CustomainerService) buildAndExportImage(jobID, baseImage string) ([]byte, error) {
	workDir := filepath.Join("/shared", jobID)

	dockerfileContent := fmt.Sprintf(`FROM %s
ADD extracted/ /uploaded/
`, baseImage)

	dockerfileName := jobID + ".Dockerfile"
	dockerfilePath := filepath.Join(workDir, dockerfileName)

	err := os.WriteFile(dockerfilePath, []byte(dockerfileContent), 0644)
	if err != nil {
		return nil, err
	}

	imageName := "customainer-" + jobID

	buildCmd := exec.Command("docker", "build", "-f", dockerfilePath, "-t", imageName, workDir)
	buildOutput, err := buildCmd.CombinedOutput()
	if err != nil {
		return nil, fmt.Errorf("docker build failed: %s", string(buildOutput))
	}

	exportCmd := exec.Command("docker", "save", imageName)
	imageData, err := exportCmd.Output()
	if err != nil {
		return nil, fmt.Errorf("docker save failed: %s", err.Error())
	}

	removeCmd := exec.Command("docker", "rmi", "-f", imageName)
	removeCmd.Run()

	return imageData, nil
}

func (cs *CustomainerService) cleanup(jobID string) {
	workDir := filepath.Join("/shared", jobID)
	os.RemoveAll(workDir)
}

func (cs *CustomainerService) handleIndex(w http.ResponseWriter, r *http.Request) {
	html := `<!DOCTYPE html>
<html>
<head>
    <title>Customainer - Custom Container Builder</title>
    <style>
        body { background-color: lightsteelblue; font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .container { background: #f5f5f5; padding: 20px; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        select, input[type="file"], button { padding: 10px; margin: 5px 0; }
        button { background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #005a87; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>🐳 Customainer</h1>
    <div class="info">
        <p><strong>Are you tired of adding your files to base images each time you make a container?</strong></p>
        <p>Upload your files as a .tar.gz archive and we'll create a custom container image for you! 
        Your files will be available under <code>/uploaded/</code> in the container.</p>
    </div>
    
    <div class="container">
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="base_image">Choose Base Image:</label><br>
                <select id="base_image" name="base_image" required>
                    <option value="">Select a base image...</option>
                    <option value="alpine:latest">Alpine Linux (latest)</option>
                    <option value="alpine:3.18">Alpine Linux 3.18</option>
                    <option value="alpine:3.17">Alpine Linux 3.17</option>
                    <option value="debian:stable-slim">Debian Stable Slim</option>
                    <option value="debian:bullseye-slim">Debian Bullseye Slim</option>
                    <option value="debian:bookworm-slim">Debian Bookworm Slim</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="archive">Upload Archive (.tar.gz):</label><br>
                <input type="file" id="archive" name="archive" accept=".tar.gz,.tgz" required>
            </div>
            
            <button type="submit">Build Custom Container</button>
        </form>
        
        <div id="status" style="margin-top: 20px;"></div>
    </div>

    <script>
        document.getElementById('uploadForm').onsubmit = function(e) {
            e.preventDefault();
            
            const status = document.getElementById('status');
            status.innerHTML = '<p>Building your custom container... Please wait.</p>';
            
            const formData = new FormData(this);
            
            fetch('/upload', {
                method: 'POST',
                body: formData
            })
            .then(response => {
				if (!response.ok) {
					return response.text().then(text => {
						throw new Error(text);
					});
				}
				return response.blob();
            })
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'custom-image.tar';
                a.click();
                status.innerHTML = '<p style="color: green;">✅ Container built successfully! Download started.</p>';
            })
            .catch(error => {
                status.innerHTML = '<p style="color: red;">❌ Build failed: ' + error.message + '</p>';
            });
        };
    </script>
</body>
</html>`

	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	w.Write([]byte(html))
}

func main() {
	service, err := NewCustomainerService()
	if err != nil {
		log.Fatal("Failed to create service:", err)
	}

	router := mux.NewRouter()
	router.HandleFunc("/", service.handleIndex).Methods("GET")
	router.HandleFunc("/upload", service.handleUpload).Methods("POST")

	log.Println("Customainer service starting on :8080")
	log.Fatal(http.ListenAndServe(":8080", router))
}
