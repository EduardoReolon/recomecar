<script>
    document.addEventListener('DOMContentLoaded', () => {
        setOnSubmitForms();
        addHashToHrefs();
    });

    function multiSelectEvents(index, default_value = '', separator = ', ') {
        const chBoxes = 
            document.querySelectorAll(`.dropdown-menu.index-multiselect-${index} input[type="checkbox"]`); 
        const dpBtn =  
            document.getElementById(`multiSelectDropdown-${index}`); 
        let mySelectedListItems = [];
    
        function handleCB() { 
            mySelectedListItems = []; 
            let mySelectedListItemsText = ''; 
    
            chBoxes.forEach((checkbox) => { 
                if (checkbox.checked) { 
                    mySelectedListItems.push(checkbox.value); 
                    mySelectedListItemsText += checkbox.getAttribute('value_to_show') + separator; 
                }
            }); 
    
            dpBtn.innerHTML = 
                mySelectedListItems.length > 0 
                    ? mySelectedListItemsText.slice(0, -2) : default_value; 
        } 
    
        chBoxes.forEach((checkbox) => { 
            checkbox.addEventListener('change', handleCB); 
        }); 
        handleCB();
    }

    /**
     * set drop area functions
     */
    function setDropArea(dropAreaId = 'drop-area', fileListId = 'file-list') {
        const dropArea = document.getElementById(dropAreaId);
        const fileList = document.getElementById(fileListId);
        
        const fileUpload = dropArea.querySelector('input[type="file"]');

        dropArea.addEventListener('click', () => {
            fileUpload.click();
        });

        dropArea.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropArea.classList.add('active');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('active');
        });

        dropArea.addEventListener('drop', (event) => {
            event.preventDefault();
            dropArea.classList.remove('active');
            
            const files = event.dataTransfer.files;
            handleFiles(files);
            
            fileUpload.files = files;
        });

        document.getElementById('file-upload').addEventListener('change', (event) => {
            const files = event.target.files;
            handleFiles(files);
        });

        function handleFiles(files) {
            fileList.innerHTML = ''; // Limpa a lista de arquivos anteriormente exibidos

            for (const file of files) {
                const listItem = document.createElement('li');
                listItem.textContent = `${file.name} (${formatBytes(file.size)})`;
                fileList.appendChild(listItem);
            }
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }

    /** @param {string|null} value */
    function castValue(value, valueIfNull = null, valueIfEmpty = '') {
        if (value === null) return valueIfNull;
        if (value === '') return valueIfEmpty;
        if (value.match(/^true$/i)) return true;
        if (value.match(/^false$/i)) return false;
        if (value.match(/^[0-9]+$/)) return parseInt(value);
        if (value.match(/^\d+\.\d+$/)) return parseFloat(value);
        return value;
    }

    /**
     * functions for tab views
     */
    function setHash(level = 0, value, onlyIfEmpty = false) {
        const activeTabHash = window.location.hash.substring(1); // Remove o '#' do hash

        const levels = activeTabHash.split('-');
        if (onlyIfEmpty) {
            if (!levels[level]) levels[level] = value;
        } else levels[level] = value;

        // window.location.hash = levels.join('-');
        history.replaceState(null, null, '#' + levels.join('-'));
    }
    function getHash() {
        return window.location.hash.substring(1).split('-');
    }
    function showTab(containerId, tabId) {
        const elements = document.querySelectorAll(`#${containerId}>.tab-pane`);
        for (let index = 0; index < elements.length; index++) {
            if (elements[index].id === `${tabId}-tab-pane`) {
                elements[index].classList.add('show');
                elements[index].classList.add('active');
            } else {
                elements[index].classList.remove('show');
                elements[index].classList.remove('active');
            }
        }
        const btn = document.getElementById(`${tabId}-tab`);
        if (btn) btn.classList.add('active');
        let level = 0;
        if (containerId === 'Content-membros') level = 1;
        setHash(level, tabId);
    }
    function addHashToHrefs() {
        document.querySelectorAll('a[add-hash]').forEach(link => {
            link.addEventListener('click', function(event) {
                // Obtém a hash atual da URL
                const currentHash = window.location.hash;

                // Obtém o href do link clicado
                const originalHref = this.getAttribute('href');

                // Atualiza o href do link com a hash atual
                const newHref = originalHref + currentHash;

                // Atualiza o href do link
                this.setAttribute('href', newHref);
            });
        });
    }

    /**
     * @param {HTMLFormElement|{action: string, method: 'post'|'get'|'patch'}} formElement
     * @param {FormData} formData ignorado se formElement for instancia de HTMLFormElement
     * @param {bool} refreshPage ignorado se formElement for instancia de HTMLFormElement
     */
    function sendForm(formElement, formData = null, refreshPage = false) {
        event.preventDefault();

        if (formElement instanceof HTMLFormElement) {
            formData = new FormData(formElement);
            refreshPage = castValue(formElement.getAttribute('refresh-page'), false, true);
            formElement.classList.add('form-disabled');
        }

        let return_value = null;

        // Exemplo de envio assíncrono dos dados usando fetch API
        fetch(formElement.action, {
            method: formElement.method,
            body: formElement.method.match(/^get$/i) ? undefined : formData,
        })
        .then(async response => {
            if (formElement instanceof HTMLFormElement) formElement.classList.remove('form-disabled');

            let serverFailureAlert = !response.ok;
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/xml')) {
                // Extrai o nome do arquivo do cabeçalho Content-Disposition
                const contentDisposition = response.headers.get('content-disposition');
                const filenameMatch = contentDisposition.match(/filename\*?=(UTF-8\'\'|")([^"]+)"?/);
                const filename = filenameMatch ? decodeURIComponent(filenameMatch[2]) : 'arquivo.xml';
                
                // Transforma a resposta em XML
                const content = await response.text();

                // Cria um blob a partir do conteúdo XML
                const blob = new Blob([content], { type: 'application/xml' });

                // Cria uma URL temporária para o blob
                const url = window.URL.createObjectURL(blob);

                // Cria um link para download do arquivo
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;

                // Clica no link para iniciar o download
                link.click();

                // Limpa a URL temporária
                window.URL.revokeObjectURL(url);
            } else if (contentType && contentType.includes('application/json')) {
                responseJson = await response.json();

                if (responseJson.redirect) {
                    window.location.href = responseJson.location;
                }

                if (Array.isArray(responseJson.alerts)) {
                    for (const alert of responseJson.alerts) {
                        sendAlert(alert.type, alert.msg, alert.time);
                        serverFailureAlert = false;
                    }
                }

                return_value = responseJson;
            }
            if (response.ok && refreshPage) return location.reload();

            if (serverFailureAlert) {
                sendAlert('warning', 'Falha no servidor!');
            }
        })
        .catch(error => {
            // Lidar com erros de requisição, como falha na conexão, etc.
            console.log(error);
            if (formElement instanceof HTMLFormElement) formElement.classList.remove('form-disabled');
            sendAlert('warning', 'Falha na página, ao salvar as alterações!');
        });

        return return_value;
    }

    /**
     * Definir onSubmit de todos os forms API
     */
    function setOnSubmitForms() {
        const forms = document.querySelectorAll('form');
        for (const form of forms) {
            // if (form.method === 'get') continue;
            if (!form.action.match(/\/api\//i)) continue;
            form.addEventListener('submit', (event) => sendForm(event.target));
        }
    }

    /**
     * @param {'primary'|'secondary'|'success'|'danger'|'warning'|'info'|'light'|'dark'} type
     */
    function sendAlert(type = 'success', msg = '', time = 30000) {
        const container = document.getElementById('conteiner_global_alerts');

        let svg_id = null;
        if (type === 'success') svg_id = 'check-circle-fill';
        else if (type === 'danger') svg_id = 'exclamation-triangle-fill';
        else if (type === 'warning') svg_id = 'exclamation-triangle-fill';
        else if (type === 'primary') svg_id = 'info-fill';
        else if (type === 'secondary') svg_id = 'info-fill';
        else if (type === 'info') svg_id = 'info-fill';

        const divAlert = document.createElement('div');
        divAlert.classList.add('alert', 'alert-dismissible', 'd-flex', 'align-items-center', `alert-${type}`);
        divAlert.role = 'alert';

        if (svg_id !== null) {
            // não está funcionando, talvez precise clonar o svg e inserir aqui
            const svg = document.createElement('svg');
            svg.classList.add('bi', 'flex-shrink-0', 'me-2');
            svg.setAttribute('width', '25');
            svg.setAttribute('height', '25');
            svg.setAttribute('role', 'img');
            svg.setAttribute('aria-label', 'Success:');
            svg.innerHTML = `<use xlink:href="#${svg_id}"/>`;
            divAlert.append(svg);
        }
        const text = document.createElement('div');
        text.innerHTML = msg;
        divAlert.append(text);

        const button = document.createElement('button');
        button.type = 'button';
        button.classList.add('btn-close');
        button.setAttribute('data-bs-dismiss', 'alert');
        button.ariaLabel = 'Close';
        divAlert.append(button);

        setTimeout(() => {
            button.click();
        }, time);

        container.append(divAlert);

        setTimeout(() => {
            container.scrollIntoView({
                behavior: 'smooth', // opcional: rolagem suave
                block: 'start' // opcional: alinha o topo do elemento com o topo da viewport
            });
        }, 100);
    }
</script>