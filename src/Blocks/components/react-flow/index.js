import React, { memo } from 'react';
import ReactFlow, { MarkerType, Controls, Background, MiniMap, Position, Handle } from 'reactflow';
import { camelize } from '@eightshift/frontend-libs/scripts';

// Create custom handle with 4 points.
const CustomHandle = memo(({data}) => {
	return (
		<>
			<Handle
				type="source"
				position={Position.Bottom}
				id="bottom-source"
				style={{
					right: 20,
					left: 'auto',
				}}
			/>
			<Handle
				type="target"
				position={Position.Bottom}
				id="bottom-target"
				style={{
					left: 20,
				}}
			/>
			<Handle
				type="source"
				position={Position.Right}
				id="right"
			/>
			<Handle
				type="target"
				position={Position.Left}
				id="left"
			/>
			<div>
				{data.label}
			</div>
		</>
	);
});

// Custom marker for the default flow.
const markerStyle = {
	type: MarkerType.ArrowClosed,
	width: 20,
	height: 20,
};

// Generate a random hex color that is not too light or too dark.
const getRandomHexColor = () => {
	const getRandomHexValue = () => Math.floor(Math.random() * 256).toString(16).padStart(2, '0');
	
	let hexColor, luminance;
	do {
		hexColor = `#${getRandomHexValue()}${getRandomHexValue()}${getRandomHexValue()}`;
		luminance = parseInt(hexColor.slice(1), 16) / (255 * 3);
	} while (!(luminance > 0.85 || luminance < 0.6));

	return hexColor;
};

// Generate the data for the react flow.
const outputMultiFlowPreviewData = (formFields, stepMultiflowRules) => {
	// Default flow with our multi-flow rules.
	// All black in one line.

	const color = '#333333';

	const edges = formFields.map(({ value }, index) => {
		const target = formFields?.[index+1]?.value;

		// If there is no target bailout.
		if (!target) {
			return null;
		}

		return {
			id: camelize(`${value}-default-flow`), // Unique ID for the edge.
			source: camelize(value),
			target: camelize(target),
			sourceHandle: 'right',
			targetHandle: 'left',
			style: {
				stroke: color,
				strokeWidth: 1.4,
			},
			markerEnd: {
				...markerStyle,
				color: color,
			},
		};
	});

	// Custom flows.
	const customFlows = stepMultiflowRules.map((item, index) => {
		const source = camelize(item?.[1].toLowerCase());
		const target = camelize(item?.[0].toLowerCase());

		const edgeColor = getRandomHexColor();

		return {
			id: camelize(`${source}-rule-${index}`), // Unique ID for the edge.
			source: source,
			target: target,
			type: 'smoothstep',
			pathOptions: {
				offset: 20 * (index + 1),
			},
			style: {
				stroke: edgeColor,
				strokeWidth: 1.4,
			},
			markerEnd: {
				...markerStyle,
				color: edgeColor,
			},
			animated: true,
			sourceHandle: 'bottom-source',
			targetHandle: 'bottom-target',
		};
	});

	// Nodes list.
	const nodes = formFields.map(({
		label,
		value,
	}, index) => {
		return {
			id: camelize(value), // Unique ID for the node.
			data: {
				label: `${label.substring(0, 40)}...`, // Limit the label to 40 characters.
			},
			type: 'selectorNode',
			sourcePosition: 'right',
			targetPosition: 'left',
			style: {
				border: `1px solid ${color}`,
				borderRadius: 5,
				padding: 10,
				width: 190,
				backgroundColor: '#ffffff',
			},
			position: {
				x: 250 * index,
				y: 50,
			},
		};
	});

	return {
		edges: [
			...edges,
			...customFlows,
		].filter(n => n),
		nodes,
	};
};

// Export multiflow forms react flow.
export const MultiflowFormsReactFlow = ({formFields, stepMultiflowRules}) => {
	const {
		edges,
		nodes,
	} = outputMultiFlowPreviewData(formFields, stepMultiflowRules);

	return (
		<div style={{ height: '100%', minWidth: '70vw', minHeight: '500px' }}>
			<ReactFlow
				nodes={nodes}
				edges={edges}
				nodeTypes={{
					selectorNode: CustomHandle,
				}}
				>
				<Background />
				<Controls />
				<MiniMap zoomable pannable />
			</ReactFlow>
		</div>
	);
};
