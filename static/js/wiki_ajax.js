class Ajax {
  constructor(apiroot="/.api") {
    this.apiroot = apiroot
  }

  // Callbacks take a single parameter, the result of the ajax
  store(path,source,callback,failcallback) {
    console.log("store_ajax",path)
    const request = { path, source }
    return this.dispatch("store",request,callback,failcallback)
  }
  source(path,callback,failcallback) {
    console.log("source_ajax",path)
    const request = { path }
    return this.dispatch("source",request,callback,failcallback)
  }
  preview(path,source,callback,failcallback) {
    console.log("previewPage ajax",path)
    const request = { path, source }
    console.log(266,{path,request})
    return this.dispatch("preview",request,callback,failcallback)
  }
  versions(path,callback,failcallback) {
    console.log("versions",path)
    const request = { path }
    return this.dispatch("versions",request,callback,failcallback)
  }
  upload(files,callback) {
    // callback handles both success, partial and error
    let n = files.length
    let form_data = new FormData()
    for( let file_obj of files ) {
      form_data.append('file[]', file_obj)
    }
    form_data.append('location',window.location.href)
    $.ajax({
      url: "/.api/upload",
      type: "POST",
      data: form_data,
      contentType: false,
      cache: false,
      processData: false,
      beforeSend: _ => {
        console.log(1234,this)
        window.ptui.infoBox.showContent(`Uploading ${n} files`)
      },
      success: data => {
        try {
          data = JSON.parse(data)
        } catch(e) {
          console.log(`Failed to parse JSON`,data)
          return window.ptui.errorBox.showContent(`Failed to parse JSON`)
        }
        return callback(data)
      },
      error: e => {
        console.log("upload error",e)
        window.ptui.errorBox.showContent(`Upload failed, see console`)
      }
    })
  }
  dispatch(endpoint,request,callback,failcallback) {
    const url = `${this.apiroot}/${endpoint}`
    console.log("ajax1",{endpoint,request})
    $.ajax({
      type: "POST",
      data: JSON.stringify(request),
      url,
      success: e => { console.log("ajax",{request,e}); callback(e) },
      dataType: "json" 
    }).fail(error => {
      const { responseText } = error
      console.log({responseText,error})
      window.req = { request, responseText }
      const errorData = {
        pagename: "ERROR",
        navbar: `Failed to load page ${request.path}`,
        tags: [],
        body: responseText.replace(/&/g,"&amp;").replace(/>/g,"&gt;").replace(/</g,"&lt;")
      }
      window.error = error
      window.responseText = responseText
      window.errorData = errorData
      if( failcallback ) failcallback(error)
    })
  }
}
