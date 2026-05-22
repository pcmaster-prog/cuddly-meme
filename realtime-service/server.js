// ==========================================
// DECORARTE MEDIA HUB + ACADEMIA
// NODE.JS MOCK API & WEBSOCKET SERVER
// ==========================================

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.static('../frontend'));

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE']
  }
});

const PORT = process.env.PORT || 5000;

// ==========================================
// IN-MEMORY MOCK STATE (Simulating Database)
// ==========================================

let activeJobs = {};

const mockUsers = {
  admin: { id: "u-1", name: "Sofía DecorArte", email: "admin@decorarte.com", role: "admin", avatar_url: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150", permissions: ["all"], xp: 1200 },
  supervisor: { id: "u-2", name: "Carlos Supervisor", email: "supervisor@decorarte.com", role: "supervisor", avatar_url: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150", permissions: ["tasks.manage", "posts.approve", "courses.view"], xp: 850 },
  editor: { id: "u-3", name: "Lucía Editora", email: "editor@decorarte.com", role: "editor", avatar_url: "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150", permissions: ["posts.create", "video.edit", "prompts.manage"], xp: 640 },
  instructor: { id: "u-4", name: "Master Chef Repostero", email: "instructor@decorarte.com", role: "instructor", avatar_url: "https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=150", permissions: ["courses.manage", "courses.grade"], xp: 950 },
  empleado: { id: "u-5", name: "Juan Cajas", email: "empleado@decorarte.com", role: "empleado", avatar_url: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150", permissions: ["tasks.view", "tasks.complete", "courses.view"], xp: 420 },
  alumno: { id: "u-6", name: "María Martínez", email: "alumno@decorarte.com", role: "alumno", avatar_url: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150", permissions: ["courses.view", "courses.take"], xp: 150 },
  visualizador: { id: "u-7", name: "Inversionista DecorArte", email: "viewer@decorarte.com", role: "visualizador", avatar_url: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150", permissions: ["analytics.view"], xp: 0 }
};

let socialAccounts = [
  { id: "sa-1", platform: "tiktok", platform_user_id: "@decorarte_oficial", username: "@decorarte_oficial", followers: "145.2K", avatar: "https://images.unsplash.com/photo-1560250097-0b93528c311a?w=80" },
  { id: "sa-2", platform: "instagram", platform_user_id: "decorarte.media", username: "decorarte.media", followers: "89.4K", avatar: "https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=80" },
  { id: "sa-3", platform: "facebook", platform_user_id: "DecorArte Oficial", username: "DecorArte Oficial", followers: "320K", avatar: "https://images.unsplash.com/photo-1580489944761-15a19d654956?w=80" },
  { id: "sa-4", platform: "youtube", platform_user_id: "DecorArte Academy", username: "DecorArte Academy", followers: "12.8K", avatar: "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=80" }
];

let scheduledPosts = [
  { id: "p-1", platform: "tiktok", status: "published", caption: "¡Aprende a decorar pasteles desde cero con DecorArte Academia! 🎂🍓 #Reposteria #Decoracion", media_urls: ["https://assets.mixkit.co/videos/preview/mixkit-decorating-a-chocolate-cake-with-strawberries-40742-large.mp4"], scheduled_for: new Date(Date.now() - 3600000 * 24).toISOString(), published_at: new Date(Date.now() - 3600000 * 23.5).toISOString(), analytics: { views: 45200, likes: 8200, shares: 450 } },
  { id: "p-2", platform: "instagram", status: "approved", caption: "Nuestras vitrinas hoy están llenas de color y sabor. ¿Cuál es tu favorito? ✨🧁", media_urls: ["https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800"], scheduled_for: new Date(Date.now() + 3600000 * 4).toISOString(), analytics: {} },
  { id: "p-3", platform: "tiktok", status: "pending", caption: "Rutina diaria de higiene y pesaje en producción. ¡Máxima calidad siempre! 🧼⚖️", media_urls: ["https://assets.mixkit.co/videos/preview/mixkit-putting-flour-in-a-scale-41887-large.mp4"], scheduled_for: new Date(Date.now() + 3600000 * 12).toISOString(), analytics: {} },
  { id: "p-4", platform: "facebook", status: "draft", caption: "Anuncio oficial: Nuevo curso de Repostería Fina presencial y digital en DecorArte Academia.", media_urls: ["https://images.unsplash.com/photo-1550617931-e17a7b70dce2?w=800"], scheduled_for: new Date(Date.now() + 3600000 * 24).toISOString(), analytics: {} }
];

let inboxMessages = [
  { id: "m-1", platform: "instagram", sender: "@pasteleria_fans", message: "Hola, ¿cuándo inicia el nuevo curso de pesaje e ingredientes?", timestamp: new Date(Date.now() - 1800000).toISOString(), read: false },
  { id: "m-2", platform: "tiktok", sender: "alex_reposteria", message: "¡Me encantó el video del tip sobre el ganache de chocolate!", timestamp: new Date(Date.now() - 3600000).toISOString(), read: true },
  { id: "m-3", platform: "facebook", sender: "Ana María", message: "Hola, ¿tienen sucursales abiertas los domingos por la tarde?", timestamp: new Date(Date.now() - 7200000).toISOString(), read: false }
];

let tasks = [
  { id: "t-1", title: "Cuadre de caja matutina", description: "Verificar arqueo de cajas y registrar discrepancias en planilla diaria.", area: "cajas", status: "completed", priority: "high", assignee: "Juan Cajas", assignee_id: "u-5", evidence_urls: ["https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=400"], completed_at: new Date(Date.now() - 7200000).toISOString() },
  { id: "t-2", title: "Pesaje de insumos para pasteles del sábado", description: "Pesar harina, azúcar, mantequilla según la receta estándar para 50 pasteles.", area: "produccion", status: "in_progress", priority: "critical", assignee: "Juan Cajas", assignee_id: "u-5", evidence_urls: [], completed_at: null },
  { id: "t-3", title: "Limpieza profunda del área de refrigeración", description: "Limpiar pisos, paredes y bandejas de refrigeradores industriales.", area: "limpieza", status: "pending", priority: "medium", assignee: "Juan Cajas", assignee_id: "u-5", evidence_urls: [], completed_at: null },
  { id: "t-4", title: "Auditar stock de empaques y cajas de cartón", description: "Verificar si es necesario realizar pedido a compras de cajas de envío medianas.", area: "almacen", status: "under_review", priority: "low", assignee: "Lucía Editora", assignee_id: "u-3", evidence_urls: ["https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=400"], completed_at: null }
];

const routines = [
  { id: "r-1", type: "apertura", title: "Rutina de Apertura de Local", days_of_week: [1, 2, 3, 4, 5, 6, 7], checklist_items: ["Encender luces y hornos", "Verificar fondo de caja", "Limpiar vitrinas frontales", "Revisar stock de pan fresco"] },
  { id: "r-2", type: "cierre", title: "Rutina de Cierre de Local", days_of_week: [1, 2, 3, 4, 5, 6, 7], checklist_items: ["Limpieza general de mesas", "Apagar hornos e interruptores", "Corte de caja final", "Activar sistema de alarma"] },
  { id: "r-3", type: "limpieza", title: "Limpieza y Desinfección Semanal", days_of_week: [1, 3, 5], checklist_items: ["Desinfectar mezcladoras", "Lavar rejillas de desagüe", "Pulir acero de mesadas"] }
];

let courses = [
  { id: "c-1", title: "Capacitación e Inducción de Empleados", description: "Protocolos operativos, cultura organizacional y valores de DecorArte.", category: "empleados", thumbnail_url: "https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=500", is_active: true, xp_reward: 200, modules_count: 2 },
  { id: "c-2", title: "Atención al Cliente Excepcional", description: "Técnicas de venta cruzada, manejo de quejas y empatía con el cliente.", category: "atencion_cliente", thumbnail_url: "https://images.unsplash.com/photo-1556742044-3c52d6e88c62?w=500", is_active: true, xp_reward: 150, modules_count: 1 },
  { id: "c-3", title: "Pesaje de Ingredientes y Fórmulas Exactas", description: "Uso correcto de la báscula digital y control de mermas en repostería.", category: "produccion", thumbnail_url: "https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500", is_active: true, xp_reward: 300, modules_count: 2 },
  { id: "c-4", title: "Repostería Fina: Bizcochos y Rellenos", description: "Aprende las recetas premium de pasteles de DecorArte.", category: "repostería", thumbnail_url: "https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=500", is_active: true, xp_reward: 500, modules_count: 3 }
];

const courseModules = {
  "c-4": [
    { id: "mod-1", title: "Introducción a los Batidos Esponjosos", order_index: 1 },
    { id: "mod-2", title: "Rellenos Clásicos y Cremas Firmes", order_index: 2 }
  ],
  "c-1": [
    { id: "mod-3", title: "Cultura DecorArte", order_index: 1 }
  ]
};

const moduleLessons = {
  "mod-1": [
    { id: "les-1", title: "Bizcochuelo de Vainilla Tradicional", content_type: "video", video_url: "https://assets.mixkit.co/videos/preview/mixkit-mixing-cake-batter-in-a-glass-bowl-41884-large.mp4", duration_minutes: 12, order_index: 1 },
    { id: "les-2", title: "Guía de Tiempos y Temperaturas de Horneado", content_type: "pdf", attachment_url: "#", duration_minutes: 5, order_index: 2 }
  ],
  "mod-2": [
    { id: "les-3", title: "Preparación de Crema de Mantequilla de Merengue Suizo", content_type: "video", video_url: "https://assets.mixkit.co/videos/preview/mixkit-hands-decorating-a-white-cake-with-cream-40741-large.mp4", duration_minutes: 18, order_index: 1 }
  ]
};

let userEnrollments = {
  "u-5": [
    { course_id: "c-1", progress_percentage: 100, completed: true, completed_at: new Date(Date.now() - 3600000 * 48).toISOString() },
    { course_id: "c-3", progress_percentage: 45, completed: false, completed_at: null }
  ],
  "u-6": [
    { course_id: "c-4", progress_percentage: 15, completed: false, completed_at: null }
  ]
};

let prompts = [
  { id: "pr-1", title: "Guión: Receta Rápida TikTok", prompt_text: "Actúa como un pastelero estrella de TikTok. Escribe un guión de 30 segundos mostrando cómo decorar una tarta de fresa con crema chantilly. Usa frases divertidas, música activa en la descripción y un CTA claro para ir a DecorArte Academia.", category: "tiktok", version: 1, is_favorite: true },
  { id: "pr-2", title: "Workflow OMNI: Inspiración Pastelera", prompt_text: "Escribe un prompt para OMNI que genere una animación cinematográfica 3D hiperrealista de una cascada de chocolate cayendo sobre un bizcocho de tres niveles con fresas glaseadas.", category: "producer", version: 2, is_favorite: false }
];

let automationRules = [
  { id: "ar-1", name: "Aprobación de Video -> Autopublicar en TikTok", is_active: true, trigger_config: { event: "video.approved" }, action_config: { action: "publish", platform: "tiktok" } },
  { id: "ar-2", name: "Retraso en Tarea -> Notificar por Email a Supervisor", is_active: false, trigger_config: { event: "task.overdue" }, action_config: { action: "notify", channels: ["email", "push"] } },
  { id: "ar-3", name: "Curso Terminado -> Entregar Diploma & 100 XP", is_active: true, trigger_config: { event: "course.completed" }, action_config: { action: "award_badge", badge: "Graduado Experto" } }
];

// ==========================================
// API ENDPOINTS
// ==========================================

// 1. Authentication
app.post('/api/v1/auth/login', (req, res) => {
  const { email, password } = req.body;
  
  // Find role based on email or default to 'empleado'
  let role = 'empleado';
  if (email.includes('admin')) role = 'admin';
  else if (email.includes('supervisor')) role = 'supervisor';
  else if (email.includes('editor')) role = 'editor';
  else if (email.includes('instructor')) role = 'instructor';
  else if (email.includes('alumno')) role = 'alumno';
  else if (email.includes('viewer')) role = 'visualizador';

  const user = mockUsers[role];
  if (user) {
    res.json({
      token: "mock-jwt-token-decorarte-" + user.id,
      user
    });
  } else {
    res.status(401).json({ message: "Credenciales inválidas" });
  }
});

app.get('/api/v1/auth/me', (req, res) => {
  const token = req.headers.authorization;
  if (!token) return res.status(401).json({ message: "No autorizado" });
  
  // Extract user role from mock token
  const userId = token.split('-').pop();
  const user = Object.values(mockUsers).find(u => u.id === userId) || mockUsers.empleado;
  res.json(user);
});

// 2. Metrics & Dashboard
app.get('/api/v1/dashboard/metrics', (req, res) => {
  res.json({
    social: {
      tiktok: { views: 120400, trend: "+12.4%", count: "145.2K" },
      instagram: { engagement: "4.2%", trend: "+1.5%", count: "89.4K" },
      facebook: { likes: 320000, trend: "+0.8%", count: "320K" },
      youtube: { watchTime: "350 hrs", trend: "+8.1%", count: "12.8K" },
      website: { visits: 85200, trend: "+18.9%", count: "85.2K" },
      whatsapp: { conversions: 870, trend: "+5.3%", count: "870 chats" }
    },
    activity: [
      { user: "Lucía Editora", action: "creó borrador de video de tarta de fresa", time: "Hace 10 min" },
      { user: "Juan Cajas", action: "completó la tarea 'Cuadre de caja matutina'", time: "Hace 2 horas" },
      { user: "María Martínez", action: "obtuvo medalla 'Iniciado en Repostería'", time: "Hace 4 horas" }
    ],
    aiSuggestions: [
      "¡El hashtag #Decoracion de repostería es tendencia en TikTok hoy! Te sugerimos publicar un reel con un bizcochuelo decorado.",
      "Un 15% de alumnos tiene dificultades con la lección 'Horneado'. Considera añadir un video de 1 minuto aclarando dudas."
    ]
  });
});

// 3. Social Media Manager
app.get('/api/v1/socials/accounts', (req, res) => {
  res.json(socialAccounts);
});

app.get('/api/v1/socials/posts', (req, res) => {
  res.json(scheduledPosts);
});

app.post('/api/v1/socials/posts', (req, res) => {
  const newPost = {
    id: "p-" + (scheduledPosts.length + 1),
    platform: req.body.platform,
    status: "pending",
    caption: req.body.caption,
    media_urls: req.body.media_urls || ["https://images.unsplash.com/photo-1550617931-e17a7b70dce2?w=800"],
    scheduled_for: req.body.scheduled_for || new Date(Date.now() + 3600000 * 24).toISOString(),
    analytics: {}
  };
  scheduledPosts.push(newPost);
  io.emit('post:created', newPost);
  res.json(newPost);
});

app.post('/api/v1/socials/posts/:id/approve', (req, res) => {
  const post = scheduledPosts.find(p => p.id === req.params.id);
  if (post) {
    post.status = "approved";
    io.emit('post:approved', post);
    res.json(post);
  } else {
    res.status(404).json({ message: "Post no encontrado" });
  }
});

app.get('/api/v1/socials/inbox', (req, res) => {
  res.json(inboxMessages);
});

app.post('/api/v1/socials/inbox/reply', (req, res) => {
  const { id, replyText } = req.body;
  const msg = inboxMessages.find(m => m.id === id);
  if (msg) {
    msg.read = true;
    res.json({ success: true, repliedMessage: msg, replyText });
  } else {
    res.status(404).json({ message: "Mensaje no encontrado" });
  }
});

// 4. Tasks & Monday board
app.get('/api/v1/tasks', (req, res) => {
  res.json(tasks);
});

app.post('/api/v1/tasks', (req, res) => {
  const newTask = {
    id: "t-" + (tasks.length + 1),
    title: req.body.title,
    description: req.body.description || "",
    area: req.body.area || "produccion",
    status: "pending",
    priority: req.body.priority || "medium",
    assignee: req.body.assignee || "Juan Cajas",
    assignee_id: req.body.assignee_id || "u-5",
    evidence_urls: [],
    completed_at: null,
    created_at: new Date().toISOString()
  };
  tasks.push(newTask);
  io.emit('task:created', newTask);
  res.json(newTask);
});

app.put('/api/v1/tasks/:id', (req, res) => {
  const task = tasks.find(t => t.id === req.params.id);
  if (task) {
    if (req.body.status) {
      task.status = req.body.status;
      if (req.body.status === 'completed') {
        task.completed_at = new Date().toISOString();
        
        // Award XP on completion
        const userId = task.assignee_id;
        const user = Object.values(mockUsers).find(u => u.id === userId);
        if (user) {
          user.xp += 25;
          io.emit('user:xp', { userId, xp: user.xp, earned: 25, reason: `Tarea completada: ${task.title}` });
        }
      }
    }
    if (req.body.evidence_urls) {
      task.evidence_urls = req.body.evidence_urls;
    }
    io.emit('task:updated', task);
    res.json(task);
  } else {
    res.status(404).json({ message: "Tarea no encontrada" });
  }
});

app.get('/api/v1/tasks/routines', (req, res) => {
  res.json(routines);
});

// 5. Academy LMS
app.get('/api/v1/academy/courses', (req, res) => {
  res.json(courses);
});

app.get('/api/v1/academy/courses/:id', (req, res) => {
  const course = courses.find(c => c.id === req.params.id);
  if (!course) return res.status(404).json({ message: "Curso no encontrado" });
  
  const modules = courseModules[course.id] || [];
  const modulesWithLessons = modules.map(m => {
    return {
      ...m,
      lessons: moduleLessons[m.id] || []
    };
  });
  
  res.json({
    course,
    modules: modulesWithLessons
  });
});

app.post('/api/v1/academy/lessons/:id/complete', (req, res) => {
  const lessonId = req.params.id;
  const { userId } = req.body;
  
  const user = Object.values(mockUsers).find(u => u.id === userId);
  if (user) {
    user.xp += 15;
    io.emit('user:xp', { userId, xp: user.xp, earned: 15, reason: `Lección completada` });
    res.json({ success: true, new_xp: user.xp });
  } else {
    res.status(404).json({ message: "Usuario no encontrado" });
  }
});

// 6. Prompts Library
app.get('/api/v1/prompts', (req, res) => {
  res.json(prompts);
});

app.post('/api/v1/prompts', (req, res) => {
  const newPrompt = {
    id: "pr-" + (prompts.length + 1),
    title: req.body.title,
    prompt_text: req.body.prompt_text,
    category: req.body.category || "tiktok",
    version: 1,
    is_favorite: false,
    created_at: new Date().toISOString()
  };
  prompts.push(newPrompt);
  res.json(newPrompt);
});

app.post('/api/v1/prompts/generate', (req, res) => {
  const { prompt } = req.body;
  // Simulates AI prompt polishing
  const polishedPrompt = `[AI ENHANCED PROMPT]\n\nRole: Expert Social Media Video Director & DecorArte Brand Ambassador.\n\nInput Context: ${prompt}\n\nTone: Modern, premium, sensory, energetic.\n\nDetailed Visual Instructions:\n- Scene 1: Macro panning shot of ingredients being poured onto a scale. Depth of field (F/1.8).\n- Scene 2: Focus on user hands folding chocolate cream. Smooth slow-motion (120fps).\n- Scene 3: Final reveal with vibrant color grading, high contrast.\n\nCTA Overlay: Escribe 'QUIERO APRENDER' en comentarios para obtener un 20% de descuento en DecorArte Academia.`;
  res.json({ polishedPrompt });
});

// 7. Video IA rendering worker queue simulation
app.post('/api/v1/production/ai/video-render', (req, res) => {
  const { title, workflow, scenes } = req.body;
  const jobId = "job-" + Math.floor(Math.random() * 1000000);
  
  activeJobs[jobId] = {
    id: jobId,
    title: title || "Video sin título",
    workflow: workflow || "TIP REPOSTERÍA",
    progress: 0,
    status: "queued"
  };

  res.json({ jobId, message: "Video render encolado satisfactoriamente." });

  // Simulate rendering process in background via Socket.io
  let progress = 0;
  activeJobs[jobId].status = "rendering";
  io.emit('job:status', activeJobs[jobId]);

  const interval = setInterval(() => {
    progress += 20;
    if (progress > 100) {
      clearInterval(interval);
      activeJobs[jobId].progress = 100;
      activeJobs[jobId].status = "completed";
      activeJobs[jobId].outputUrl = "https://assets.mixkit.co/videos/preview/mixkit-decorating-a-chocolate-cake-with-strawberries-40742-large.mp4";
      
      // Auto register to post library (Trigger automation simulation)
      const autoPost = {
        id: "p-" + (scheduledPosts.length + 1),
        platform: "tiktok",
        status: "pending",
        caption: `¡Vídeo generado automáticamente para el workflow ${activeJobs[jobId].workflow}! 🎂🎥 #DecorArte #IA`,
        media_urls: [activeJobs[jobId].outputUrl],
        scheduled_for: new Date(Date.now() + 3600000 * 2).toISOString(),
        analytics: {}
      };
      scheduledPosts.push(autoPost);
      io.emit('post:created', autoPost);
      
      io.emit('job:status', activeJobs[jobId]);
      delete activeJobs[jobId];
    } else {
      activeJobs[jobId].progress = progress;
      io.emit('job:status', activeJobs[jobId]);
    }
  }, 2000); // Progress every 2 seconds
});

// 8. Automations rules
app.get('/api/v1/automations/rules', (req, res) => {
  res.json(automationRules);
});

app.post('/api/v1/automations/rules', (req, res) => {
  const newRule = {
    id: "ar-" + (automationRules.length + 1),
    name: req.body.name,
    is_active: true,
    trigger_config: req.body.trigger_config || {},
    action_config: req.body.action_config || {}
  };
  automationRules.push(newRule);
  res.json(newRule);
});

app.post('/api/v1/automations/rules/:id/toggle', (req, res) => {
  const rule = automationRules.find(r => r.id === req.params.id);
  if (rule) {
    rule.is_active = !rule.is_active;
    res.json(rule);
  } else {
    res.status(404).json({ message: "Regla no encontrada" });
  }
});

// ==========================================
// SOCKET.IO CONNECTIONS
// ==========================================
io.on('connection', (socket) => {
  console.log(`Cliente conectado: ${socket.id}`);
  
  socket.on('disconnect', () => {
    console.log(`Cliente desconectado: ${socket.id}`);
  });
});

// Start listening
server.listen(PORT, () => {
  console.log(`Servidor de DecorArte corriendo en http://localhost:${PORT}`);
});
