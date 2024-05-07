<?php

namespace App;

use App\Renderers\TabsRenderer;
use Chewie\Concerns\CreatesAnAltScreen;
use Chewie\Concerns\RegistersRenderers;
use Chewie\Input\KeyPressListener;
use Laravel\Prompts\Prompt;

class Tabs extends Prompt
{
    use RegistersRenderers;
    use CreatesAnAltScreen;

    public array $tabs = [
        [
            'tab'     => 'About Me',
            'content' => "Hello! I'm Joe, a dedicated software engineer with a passion for crafting clean, efficient, and user-friendly applications. With a background in computer science and years of experience in the tech industry, I thrive on collaborating with teams to turn ideas into functional software solutions.\n\nI believe that innovation drives progress, and I am constantly exploring new technologies to stay ahead of the curve. When I'm not coding, you can find me exploring the intersection of art and technology or enjoying the great outdoors. Let's connect and create something amazing together!",
        ],
        [
            'tab'     => 'Skills',
            'content' => "I bring a diverse skill set to the table as a software engineer. My expertise spans multiple programming languages, including PHP, Python, and JavaScript, allowing me to tackle a wide range of projects.\n\nI excel in developing web applications using top frameworks like React and Angular, and I am adept at managing databases with proficiency in SQL and NoSQL. With a solid foundation in data structures, algorithms, and software development principles, I approach challenges with analytical precision. I am well-versed in cloud computing services such as AWS and Azure, enabling me to leverage the latest technologies for efficient solutions.\n\nA strong problem solver with attention to detail, I thrive in collaborative environments and excel at communication. Continuously seeking to expand my knowledge and stay at the forefront of innovation, I am passionate about driving impactful change through technology."
        ],
        [
            'tab'     => 'Projects',
            'content' => "E-Commerce Platform Development\n\nLed a cross-functional team in the successful development of an e-commerce platform from concept to deployment. Implemented key features such as user authentication, product catalog management, and secure payment processing. Utilized React and Node.js to create a responsive and scalable web application that significantly enhanced the user experience.\n\nData Visualization Dashboard\n\nDesigned and implemented a custom data visualization dashboard for a client to track and analyze performance metrics. Leveraged D3.js for interactive data visualizations and integrated with a backend API to fetch real-time data. The dashboard provided actionable insights and was praised for its intuitive design and robust functionality.",
        ],
        [
            'tab'     => 'Contact',
            'content' => "Let's connect and discuss how we can work together to bring your ideas to life.\n\nWhether you have a project in mind or just want to chat about technology, I'm always open to new opportunities and collaborations.",
        ],
    ];

    public int $selectedTab = 0;

    public function __construct()
    {
        $this->registerRenderer(TabsRenderer::class);

        $this->createAltScreen();

        KeyPressListener::for($this)
            ->listenForQuit()
            ->onRight(fn () => $this->selectedTab = min($this->selectedTab + 1, count($this->tabs) - 1))
            ->onLeft(fn () => $this->selectedTab = max($this->selectedTab - 1, 0))
            ->listen();
    }

    public function value(): mixed
    {
        return null;
    }
}
