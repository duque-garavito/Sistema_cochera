        <header>
            <h1>🏢 Sistema de Control de Vehículos</h1>
            <nav>
                <a href="index.php" class="nav-link active">Registro</a>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="reportes.php" class="nav-link">Reportes</a>
                <a href="vehiculos.php" class="nav-link">Vehículos</a>
                <a href="logout.php" class="nav-link">🚪 Salir</a>
            </nav>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                (<?php echo ucfirst($_SESSION['rol']); ?>)</span>
            </div>
        </header>