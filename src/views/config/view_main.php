<?php
require_once 'view_core.php';


class View_main extends View_core {
    private function body_start() {
        $uri = Helper::getCurrentUri();
        ?>
                <style>
                    body {
                        background-color: #fbfbfb;
                    }
                    @media (min-width: 991.98px) {
                        main {
                            padding-left: 240px;
                        }
                    }

                    /* Sidebar */
                    .sidebar {
                        position: fixed;
                        top: 0;
                        bottom: 0;
                        left: 0;
                        padding: 58px 0 0; /* Height of navbar */
                        box-shadow: 0 2px 5px 0 rgb(0 0 0 / 5%), 0 2px 10px 0 rgb(0 0 0 / 5%);
                        width: 240px;
                        z-index: 600;
                    }
                    #page-content-wrapper{
                        padding-left: 240px;
                    }
                </style>
                <div id="wrapper">
                    <div id="sidebar-wrapper" class="collapse d-lg-block sidebar collapse bg-white">
                        <nav class="collapse d-lg-block sidebar collapse bg-white">
                            <div class="position-sticky">
                                <div class="list-group list-group-flush mx-3 mt-4">
                                    <a href="<?php echo Helper::uriRoot('/') ?>" class="list-group-item list-group-item-action py-2 ripple<?php echo $uri === '/' ? ' active' : ''; ?>" aria-current="true">
                                        <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Página inicial</span>
                                    </a>
                                </div>
                            </div>
                        </nav>
                    </div>
                    <main id="page-content-wrapper">
                        <div id="conteiner_global_alerts"></div>
        <?php
    }


    private function body_end() {
        ?>
                    </main>
                </div>
            </html>
        <?php
    }

    protected function body_content() {
        ?>
            <h1>Página não encontrada</h1>
        <?php
    }

    protected function body() {
        ?>
            <body>
                <?php $this->body_start() ?>
                <?php $this->body_content() ?>
                <?php $this->body_end() ?>
            </body>
        <?php
    }
}