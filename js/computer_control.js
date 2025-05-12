function changeLab(labName) {
    document.querySelectorAll(".lab-layout").forEach(lab => {
        lab.style.display = "none";
    });
    if (labName) {
        const selectedLab = document.getElementById("lab-" + labName);
        if (selectedLab) {
            selectedLab.style.display = "block";
        }
    }
}

function refreshPCStatuses() {
    const labSelect = document.getElementById("labSelect");
    if (labSelect && labSelect.value) {
        const labName = labSelect.value;
        fetch("get_pc_status.php?lab=" + labName)
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    const labLayout = document.getElementById("lab-" + labName);
                    if (labLayout) {
                        data.pcs.forEach(pc => {
                            const pcItem = labLayout.querySelector("[data-pc-number=\'" + pc.pc_number + "\']");
                            if (pcItem) {
                                pcItem.setAttribute("data-status", pc.status);
                                const icon = pcItem.querySelector(".pc-icon");
                                if (icon) {
                                    switch(pc.status) {
                                        case "available":
                                            icon.textContent = "💻";
                                            break;
                                        case "offline":
                                            icon.textContent = "📺";
                                            break;
                                        case "maintenance":
                                            icon.textContent = "🔧";
                                            break;
                                    }
                                }
                                pcItem.className = "pc-item";
                                pcItem.classList.add("status-" + pc.status);
                                const select = pcItem.querySelector("select");
                                if (select) {
                                    select.value = pc.status;
                                }
                            }
                        });
                        
                        const availablePCs = data.pcs.filter(pc => pc.status === "available").length;
                        const titleElement = labLayout.querySelector("h2");
                        if (titleElement) {
                            titleElement.textContent = labName + " - Available PCs: " + availablePCs + "/50";
                        }
                    }
                }
            })
            .catch(error => console.error("Error refreshing PC statuses:", error));
    }
}

// Handle individual PC status form submission
document.querySelectorAll(".status-form").forEach(form => {
    form.addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch("set_pc_status.php", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (response.status === 204) {
                const pcNumber = formData.get("pc_number");
                const lab = formData.get("lab");
                const status = formData.get("status");
                
                const pcItem = document.querySelector("[data-pc-number=\'" + pcNumber + "\']");
                if (pcItem) {
                    // Update status
                    pcItem.setAttribute("data-status", status);
                    
                    // Update icon
                    const icon = pcItem.querySelector(".pc-icon");
                    if (icon) {
                        switch(status) {
                            case "available":
                                icon.textContent = "💻";
                                break;
                            case "offline":
                                icon.textContent = "📺";
                                break;
                            case "maintenance":
                                icon.textContent = "🔧";
                                break;
                        }
                    }
                    
                    // Update border color
                    pcItem.className = "pc-item";
                    pcItem.classList.add("status-" + status);
                    
                    // Update select value
                    const select = pcItem.querySelector("select");
                    if (select) {
                        select.value = status;
                    }
                }
                
                // Update available PCs count
                const labLayout = document.getElementById("lab-" + lab);
                if (labLayout) {
                    const availablePCs = labLayout.querySelectorAll("[data-status=\'available\']").length;
                    const titleElement = labLayout.querySelector("h2");
                    if (titleElement) {
                        titleElement.textContent = lab + " - Available PCs: " + availablePCs + "/50";
                    }
                }
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });
});

// Handle lab status form submission
document.querySelectorAll(".lab-status-form").forEach(form => {
    form.addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch("set_pc_status.php", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (response.status === 204) {
                const lab = formData.get("lab");
                const status = formData.get("status");
                
                const labLayout = document.getElementById("lab-" + lab);
                if (labLayout) {
                    const pcItems = labLayout.querySelectorAll(".pc-item");
                    pcItems.forEach(pcItem => {
                        // Update status
                        pcItem.setAttribute("data-status", status);
                        
                        // Update icon
                        const icon = pcItem.querySelector(".pc-icon");
                        if (icon) {
                            switch(status) {
                                case "available":
                                    icon.textContent = "💻";
                                    break;
                                case "offline":
                                    icon.textContent = "📺";
                                    break;
                                case "maintenance":
                                    icon.textContent = "🔧";
                                    break;
                            }
                        }
                        
                        // Update border color
                        pcItem.className = "pc-item";
                        pcItem.classList.add("status-" + status);
                        
                        // Update select value in admin controls
                        const select = pcItem.querySelector("select");
                        if (select) {
                            select.value = status;
                        }
                    });
                    
                    // Update available PCs count
                    const availablePCs = status === "available" ? 50 : 0;
                    const titleElement = labLayout.querySelector("h2");
                    if (titleElement) {
                        titleElement.textContent = lab + " - Available PCs: " + availablePCs + "/50";
                    }
                }
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });
});

// Handle select changes directly
document.querySelectorAll(".pc-admin-controls select").forEach(select => {
    select.addEventListener("change", function() {
        this.form.dispatchEvent(new Event("submit"));
    });
});

document.querySelectorAll(".lab-admin-controls select").forEach(select => {
    select.addEventListener("change", function() {
        this.form.dispatchEvent(new Event("submit"));
    });
});

setInterval(refreshPCStatuses, 5000); 