function Avatarcrop() {
  return {
    visible: false,
    inline: false,
    filename: "",
    mimeType: "",
    dataUrl: null,
    file: null,
    cropper: null,
    cancel() {
      this.file = null;
      this.dataUrl = null;
    },
    upload() {
      this.cropper
        .getCroppedCanvas({ width: 128, height: 128 })
        .toBlob((blob) => this.upload_blob(blob), "image/png", 1);
    },
    upload_blob(blobData) {
      fetch(`/upload/avatar`, { method: "POST", body: blobData })
        .then((response) => {
          if (response.ok) return response;
          else
            throw Error(
              `Server returned ${response.status}: ${response.statusText}`
            );
        })
        .then((response) => {
          console.log(response.text())
          window.location.reload()
        })
        .catch((err) => {
          alert(err);
        });
    },
    onImgElementError() {
      console.log("error loading image");
    },
    onFileChange(file) {
      console.log("input", file);
      const reader = new FileReader();
      reader.onload = (e) => {
        this.dataUrl = e.target.result;
      };
      this.file = file;
      reader.readAsDataURL(file);

      this.filename = file.name || "unknown";
      this.mimeType = this.mimeType || file.type;
    },
    onFileInputChange(e) {
      if (!e.target.files || !e.target.files[0]) return;

      this.onFileChange(e.target.files[0]);
    },
    pickImage() {
      if (this.$refs.input) this.$refs.input.click();
    },
    create_cropper() {
      this.cropper = new Cropper(this.$refs.img, {
        aspectRatio: 1,
        autoCropArea: 1,
        viewMode: 1,
        movable: false,
        zoomable: false,
      });
    },
    mounted() {
      if (this.file) {
        this.onFileChange(file);
      }
      console.log("mouted avatarcropper", this.file);
    },
  };
}
