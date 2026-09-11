# Arqueologia e Restauracao - Loading Historico e loaderpp.gif

Data: 2026-09-11  
Status: [CONFIRMADO-14.6.1] / [CONFIRMADO-15.8.1]  
Identificador: `ARQ-RMA-LOADER-001`  

---

## 1. Contexto e Investigacao

Em 2026-09-11, o dono solicitou revisao no legado sobre o componente/mecanismo de "loading",
suspeitando que nao existisse mais no sistema reconstruido.

A investigacao no backup oficial 15.9.7 (`~/github/_rma-arqueologia/backup-15.9.7/`) e nos repositorios
historicos (`14.10.2` e `15.10.1`) confirmou a existencia de um mecanismo canonico de loading
utilizado em ambos os aplicativos legados (14.6.1 e 15.8.1).

---

## 2. Evidencias Encontradas no Codigo Legado

### 2.1 O Asset Historico `loaderpp.gif`
- **Arquivo**: `app/15.9.7/loaderpp.gif`
- **Tamanho**: 132.171 bytes (~129 KB)
- **Dimensoes**: 100 x 100 pixels (GIF animado versao 89a)
- **SHA-256**: `df82f081eb6b0dce529ebe13b1907d92eb83a3c9068401a78dad2caeff5fff06`
- O backup tambem contem um diretorio `app/15.9.7/framework/loader/` contendo variacoes numeradas
  (`loading1.gif` a `loading13.gif`, `loaderpingpong.gif`, `loaderprompt.gif`), porem o unico asset
  ativamente instanciado nas views principais e `loaderpp.gif`.

### 2.2 Tema V1 (Legacy 14.6.1)
- **Arquivo**: `14.6.1/index.php` (linhas 178-181 e 217):
  ```html
  <div id="BASE">
      <div id="loader" class="loader"><img src="../loaderpp.gif"/></div>
      <div id="hidden" style="display: none;">
          <div id="MEIO">
              ...
          </div>
      </div>
  </div>
  <script>document.getElementById('hidden').style.display = "block"; document.getElementById('loader').style.display = "none";</script>
  ```
- **Estilo em `pattern/14.6.1.css`** (linha 60):
  ```css
  .loader { width:1004px;margin:0 auto; }
  ```
- **Script em `pattern/14.6.1.js`** (linhas 1-13):
  ```javascript
  window.onload = function() {
      tableZebrada("zebrada");
      defer();
      acentuacao();
  }
  function defer() {
      document.getElementById('hidden').style.display = "block";
      document.getElementById('loader').style.display = "none";
  }
  ```

### 2.3 Tema V2 (Legacy 15.8.1)
- **Arquivo**: `15.8.1/index.php` (linhas 46-93 e 97-116):
  ```html
  <div class="container" style="float:left;">
      <div id="loader" class="loader" style="margin-left:15px;margin-top:45px;">
          <img src="../loaderpp.gif" title="Loading" alt="Loading"/>
      </div>
      <div id="hidden" style="display: none;">
          ...
      </div>
      <script>
          document.getElementById('hidden').style.display = "block";
          document.getElementById('loader').style.display = "none";
      </script>
  </div>
  <div id="loader_r" class="upmenuright" style="border:0px;background-color:rgba(0,0,0,0);padding:0px;margin:0px;margin-top:45px;font-weight:normal;">
      <img src="<?php echo $local; ?>loaderpp.gif" title="Loading" alt="Loading"/>
  </div>
  <script>document.getElementById('loader_r').style.display = "block";</script>
  <div id="menuright" ... style="display:none;...">...</div>
  <script> document.getElementById('loader_r').style.display  = "none"; </script>
  <script> document.getElementById('menuright').style.display = "block"; </script>
  ```
- **Estilo em `pattern/15.8.1.css`** (linhas 463-466):
  ```css
  .loader {
      width:900px;
      margin:15px auto;
  }
  ```

---

## 3. Diagnostico no Sistema Modernizado

O sistema modernizado nao continha:
1. O asset `loaderpp.gif` em `public/images/`.
2. A declaracao de `#loader` nem `.loader` em `resources/views/temas/v1/layout.blade.php`.
3. A declaracao de `#loader` nem `#loader_r` em `resources/views/temas/v2/layout.blade.php`.
4. As regras CSS `.loader` em `_v1-base.scss` e `_v2-base.scss`.
5. A funcao `defer()` em `v1.js` e helpers de exibicao/ocultacao em `v2.js`.

---

## 4. Implementacao da Restauracao

1. **Asset transferido**: Copiado fielmente de `backup-15.9.7` para `public/images/loaderpp.gif` e `public/loaderpp.gif`.
2. **CSS nos dois temas**:
   - V1: `.loader { width: 1004px; margin: 0 auto; text-align: center; }`
   - V2: `.loader { width: 900px; margin: 15px auto; text-align: center; }` e `#loader_r`.
3. **Blade Layouts**:
   - V1: insercao de `#loader` com `.loader` dentro de `#BASE`, antes de `#hidden` contendo `#MEIO`.
   - V2: insercao de `#loader` e `#loader_r` com a geometria e margens historicas (`margin-top: 45px`).
4. **JavaScript autoral**:
   - `window.defer()`, `window.mostrarLoader()` e `window.ocultarLoader()` disponibilizados nos scripts de ambos os temas.
5. **Garantia de nao quebra**: `#loader` e inicializado com `display: none;` no template Blade para preservar a renderizacao rapida e os testes SSR, podendo ser invocado client-side para simulacao, transicoes e operacoes longas.
