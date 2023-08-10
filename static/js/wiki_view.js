class PTUIView extends PTUI {
  constructor(ajax) {
    super(ajax)
    this.signalCopyTime = 1500
  }
  setupUI() {
    super.setupUI()
    const { q, qq } = window
    function c(sel,cb) {
      qq(sel).forEach(elt => {
        elt.addEventListener("click",e => {
          e.preventDefault()
          cb(e)
        })
      })
    }
    c(".action.duplicate", e => this.duplicateView())
    c(".action.duplicate-edit",e => this.duplicateEdit())
    c(".action.versions",e => this.showVersions() )
    c(".action.show-goto-box",e => this.gotoBox.show() )
    c(".action.hamburger", e => this.toggleOptionsBar("hamburger"))
    // c(".action.more-actions", e => this.toggleOptionsBar("more-actions"))

    this.linksInBody = undefined
    this.currentLinkIndex = 0
    this.quickKeys = new Map()
    this.keys.next = e => {
      const k = e.key
      if( this.quickKeys.has(k) ) {
        const { href } = this.quickKeys.get(k)
        window.location.href = href
      }
    }

  }
  showVersions() {
    // show in a dialog
    let { local: path } = this.getUriInfo()
    console.log("showVersions",{path})
    this.ajax.versions(path,e => this.didGetVersions(e),e => this.failedToGetVersions(e))
  }
  didGetVersions(e) {
    console.log("Got versions",e)
    let path = e.path.replace(/\.ptmd$/,"")
    let pagename = path.replace(/^.*\//,"")
    this.versionsBox.showVersions(pagename,path,e.versions)
    //this.infoBox.showContent(div)
  }
  failedToGetVersions(e) {
    console.log("Failed to get versions 183",e)
    let div = document.createElement("div")
    let h1 = document.createElement("h1")
    h1.textContent = "Failed to get versions"
    div.append(h1)
    let pre = document.createElement("pre")
    pre.textContent = e.responseText
    div.append(pre)
    this.errorBox.showContent(div)
  }
  unimplemented(e) {
    console.warn("Unimplemented",e)
  }
  toggleOptionsBar(barName) {
    console.log("toggle options",barName)
    let currentOptionsBar = document.body.getAttribute("options-bar")
    if( currentOptionsBar === barName ) {
      document.body.removeAttribute("options-bar")
    } else {
      document.body.setAttribute("options-bar",barName)
    }
  }
  setupKeys() {
    super.setupKeys()
    const f = (t,d,h) => this.keys.addfp(t,d,h)
    const n = (t,d,h) => this.keys.addnp(t,d,h)

    n("C-`","edit page",e => this.editPage())
    n("S-`","edit page",e => this.editPage())
    n("S-c","copy selected pre", e => {
      if( this.selectedPreCode ) {
        this.copyTextFrom(this.selectedPreCode)
      }
    })
    n("A-C-c","copy source", e => {
      let { local: path } = this.getUriInfo()
      this.ajax.source(path,e => {
        let { source } = e
        let clipboard = navigator.clipboard
        if( ! clipboard ) {
          console.warn("No clipboard")
          return
        }
        navigator.clipboard.writeText(source)
        this.infoBox.showContent("Copied source")
      }, e => {
        this.errorBox.showContent("Failed to get source")
      })
    })
    n("S-w","toggle pre wrap", e => {
      document.body.classList.toggle("pre-wrap")
    })
    n("S-arrowup","go to parent dir", e => {
      window.location.href = "../home"
    })
    n("S-h","goto home in current dir",e => {
      window.location.href = "home"
    })
    n("C-/","goto root",e => {
      window.location.href = "/"
    })
    n("S-g","open goto box", e => {
      this.gotoBox.newTab = false
      this.gotoBox.show()
    })
    n("arrowleft","prev link in body", e => this.prevLinkInBody())
    n("arrowright","next link in body", e => this.nextLinkInBody())
    n("S-l","show quick keys", e => this.showQuickKeys())
    n("S-t","toggle touch mode", e => {
      document.body.classList.toggle("touch-mode")
    })
    n("S-v","show versions", e => this.showVersions())
    n("S-d","directory listing of current subdir", e => window.location.href=".dir")
    n("S-r","recent changes in current subdir", e => window.location.href=".recent")
  }
  showQuickKeys() {
    let div = document.createElement("div")
    let h1 = document.createElement("h1")
    h1.textContent = "Quick Keys"
    div.append(h1)
    let table = document.createElement("table")
    div.append(table)
    table.classList.add("quick-keys")
    let ks = this.quickKeys.keys()
    for( k of ks ) {
      const { name, href } = this.quickKeys.get(k)
      let tr = document.createElement("tr")
      let td, a
      td = document.createElement("td")
      td.classList.add("key")
      td.textContent = k
      tr.append(td)
      td = document.createElement("td")
      td.classList.add("name")
      td.textContent = name
      tr.append(td)
      td = document.createElement("td")
      td.classList.add("href")
      a = document.createElement("a") 
      a.setAttribute("href",href)
      a.textContent = href
      td.append(a)
      tr.append(td)
      table.append(tr)
    }
    this.infoBox.showContent(div,10000)
  }
  prevLinkInBody() {
    if( ! this.linksInBody ) {
      this.linksInBody = qq("section.main a")
      this.currentLinkIndex = 0
    }
    if( this.linksInBody.length == 0 ) return console.log("no links in body")
    const i = this.linksInBody.length + this.currentLinkIndex - 1
    this.currentLinkIndex = i % this.linksInBody.length
    this.linksInBody[this.currentLinkIndex].focus()
  }
  nextLinkInBody() {
    if( ! this.linksInBody ) {
      this.linksInBody = qq("section.main a")
      this.currentLinkIndex = 0
    }
    if( this.linksInBody.length == 0 ) return console.log("no links in body")
    const i = this.linksInBody.length + this.currentLinkIndex + 1
    this.currentLinkIndex = i % this.linksInBody.length
    this.linksInBody[this.currentLinkIndex].focus()
  }
  editPage() {
    let href = this.hereWithAction("edit")
    window.location.href = href
  }
  compileQuickKeys() {
    this.quickKeys = new Map()
    const links = qq("a")
    links.forEach(link => {
      const n = link.nextSibling
      let m
      if( n && ( m = n.textContent.match(/\[([a-z0-9])\]/) ) ) {
        const ntc = n.textContent
        let newTextContent = ntc.substr(3)
        let span = document.createElement("span")
        span.classList.add("quick-key")
        span.textContent = ntc.substr(0,3)
        n.parentNode.insertBefore(span,n)
        n.textContent = newTextContent
        const key = m[1]
        const name = link.textContent
        const href = link.getAttribute("href")
        this.quickKeys.set(key, { name, href })
      }
    })
  }
  copyTextFrom(pre) {
    const text = pre.textContent
    let clipboard = navigator.clipboard
    if( ! clipboard ) {
      console.warn("No clipboard")
      return
    }
    navigator.clipboard.writeText(text)
    this.signalCopy(pre)
  }
  signalCopy(elt) {
    let summary = elt.textContent
    elt.setAttribute("select-status","copied")
    if( summary.length > 100 ) {
      summary = summary.substr(0,100)+"..."
    }
    this.infoBox.showContent(`Copied: ${summary}`,this.signalCopyTime)
    setTimeout(_ => { 
      elt.removeAttribute("select-status")
    },this.signalCopyTime)
  }
  handleClick(e) {
    let elt = e.target
    this.last = elt
    if( !elt.tagName ) return
    if( this.lastpre ) {
      this.lastpre.classList.remove("jda-selected")
    }
    while( elt.tagName.toLowerCase() !== "BODY" &&
           elt.tagName.toLowerCase() !== "PRE" ) {
      elt = elt.parentElement
      if( ! elt ) return
    }
    if( elt.tagName === "PRE" ) {
      this.lastpre = elt
      this.lastpre.classList.add("jda-selected")
    } else {
      this.lastpre = undefined
    }
  }
  init() {
    super.init()
    this.compileQuickKeys()
    if(hljs) hljs.highlightAll(); else console.warn("no hljs")
    qq("pre > code").map(x => x.parentElement).map(x => x.addEventListener("click", e => {
      if( this.selectedPreCode ) { this.selectedPreCode.removeAttribute("select-status") }
      this.selectedPreCode = x
      x.setAttribute("select-status","selected")
}))
  }
}


window.addEventListener("load", _ => {
  const ajax = new Ajax()
  const ui = new PTUIView(ajax)
  ui.init()
})
